#!/bin/bash

# EXCLUDE TMA BRANCH
IS_TMA=`echo $CI_COMMIT_TAG | grep -o '[.]*_TMA[.]*'`

if [ -z $IS_TMA ]; then

    tag=$CI_COMMIT_TAG

    echo "tag:$tag"

    structures=$(echo $CI_COMMIT_TAG | tr "." "\n")

    IT=1
    for item in $structures
    do
        if [ $IT = 1 ]; then
            major_version=$item
        fi

        if [ $IT = 2 ]; then
            major_version="$major_version.$item"
        fi

        if [ $IT = 3 ]; then
            current_num_tag=$item
        fi

        IT=$((IT+1))
    done

    previous_num_tag=$((current_num_tag-1))
    next_num_tag=$((current_num_tag+1))

    previous_tag="$major_version.$previous_num_tag"
    next_tag="$major_version.$next_num_tag"

    echo "previoustag:$previous_tag"

    for row in $(curl --header "PRIVATE-TOKEN: $TOKEN_GITLAB" "https://labs.maarch.org/api/v4/projects/$CI_PROJECT_ID/milestones?title=$previous_tag" | jq -r '.[] | @base64'); do
        _jq() {
        echo ${row} | base64 --decode | jq -r ${1}
        }

        ID=$(_jq '.id')

        echo $ID

        BODY="{\"id\":\"$ID\",\"state_event\":\"close\"}"
        
        curl -v -H 'Content-Type:application/json' -H "PRIVATE-TOKEN:$TOKEN_GITLAB" -d "$BODY" -X PUT https://labs.maarch.org/api/v4/projects/$CI_PROJECT_ID/milestones/$ID

    done

    BODY="{\"id\":\"$CI_PROJECT_ID\",\"title\":\"$next_tag\"}"

    # CREATE NEXT TAG MILESTONE
    curl -v -H 'Content-Type:application/json' -H "PRIVATE-TOKEN:$TOKEN_GITLAB" -d "$BODY" -X POST https://labs.maarch.org/api/v4/projects/$CI_PROJECT_ID/milestones

    # GENERATE RAW CHANGELOG
    file="tmp.txt"
    file2="tmp2.txt"
    file3="tmp3.txt"
    file4="tmp4.txt"

    CONTENT=""

    cd ci

    mkdir tmp

    cd tmp

    echo "Set user git : $GITLAB_USER_NAME <$GITLAB_USER_EMAIL>"

    git config --global user.email "$GITLAB_USER_EMAIL" && git config --global user.name "$GITLAB_USER_NAME"

    git clone $REPOSITORY_URL_SSH -b $major_version .

    git fetch

    echo "git log $previous_tag..$CI_COMMIT_TAG --pretty=format:'%s' --grep='Update referential' --all-match";

    REFUPDATED=`git log $previous_tag..$CI_COMMIT_TAG --pretty=format:'%s' --grep='Update referential' --all-match`

    echo "git log $previous_tag..$CI_COMMIT_TAG --pretty=format:'%s' --grep='FEAT' --all-match";

    git log $previous_tag..$CI_COMMIT_TAG --pretty=format:'%s' --grep='FEAT' --all-match > tmp.txt
    echo '' >> tmp.txt

    while IFS= read -r line
    do
        ISSUE_ID=`echo $line | grep -o 'FEAT #[0-9]*' | grep -o '[0-9]*'`
        echo "$ISSUE_ID" >> tmp2.txt
    done <"$file"

    echo "git log $previous_tag..$CI_COMMIT_TAG --pretty=format:'%s' --grep='FIX' --all-match";

    git log $previous_tag..$CI_COMMIT_TAG --pretty=format:'%s' --grep='FIX' --all-match > tmp.txt
    echo '' >> tmp.txt

    while IFS= read -r line
    do
        ISSUE_ID=`echo $line | grep -o 'FIX #[0-9]*' | grep -o '[0-9]*'`
        echo "$ISSUE_ID" >> tmp2.txt
    done <"$file"

    sort -u $file2 > tmp3.txt

    while IFS= read -r line
    do
        echo "=================="
        echo $line
        curl -H "X-Redmine-API-Key: ${REDMINE_API_KEY}" -H 'Content-Type: application/json' -X GET https://forge.maarch.org/issues/$line.json > issue_$line.json
        # echo `cat issue_$line.json`
        SUBJECT=`cat issue_$line.json | jq -r '.issue.subject'`
        TRACKER=`cat issue_$line.json | jq -r '.issue.tracker.name'`
        ID=`cat issue_$line.json | jq -r '.issue.id'`
        echo ""
        echo "ID : $ID"
        echo "TRACKER : $TRACKER"
        echo "SUBJECT : $SUBJECT"
        echo "=================="

        if [ ! -z $ID ]
        then
            echo "* **$TRACKER [#$ID](https://forge.maarch.org/issues/$ID)** - $SUBJECT" >> tmp4.txt
        fi
    done <"$file3"

    if [[ !  -z  $REFUPDATED  ]]; then
        echo "* **Fonctionnalité** - Mise à jour de la BAN 75" >> tmp4.txt
    fi

    sort -u $file4 >> changelog.txt

    while IFS= read -r line
    do
        CONTENT="$CONTENT\n$line"
    done <"changelog.txt"

    echo $CONTENT

    # Update tag release
    BODY="{\"description\":\"$CONTENT\"}"

    curl -v -H 'Content-Type:application/json' -H "PRIVATE-TOKEN:$TOKEN_GITLAB" -d "$BODY" -X POST https://labs.maarch.org/api/v4/projects/$CI_PROJECT_ID/repository/tags/$CI_COMMIT_TAG/release


    # NOTIFY TAG IN SLACK
    curl -X POST --data-urlencode "payload={\"channel\": \"$CHANNEL_SLACK_NOTIFICATION\", \"username\": \"$USERNAME_SLACK_NOTIFICATION\", \"text\": \"Jalon mis à jour à la version $tag!\nVeuillez rédiger le <$CI_PROJECT_URL/tags/$tag/release/edit|changelog> et définir une date de sortie.\", \"icon_emoji\": \":cop:\"}" $URL_SLACK_NOTIFICATION

    # Update files version
    cp package.json tmp_package.json

    jq -r ".version |= \"$next_tag\"" tmp_package.json > package.json

    rm tmp_package.json

    git add -f package.json

    # sed -i -e "s/$CI_COMMIT_TAG/$next_tag/g" sql/test.sql

    # git add -f sql/test.sql

    git commit -m "Update next tag version files : $next_tag"

    git push

fi