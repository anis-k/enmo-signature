#!/bin/bash

# Synchronize commits to the private repository by merging commits in the public repository to the private one

echo "Synchronizing changes in branch $CI_COMMIT_REF_NAME"

git status

git remote set-url origin $PRIVATE_REPOSITORY_URL_SSH
git config --global user.email "$CI_EMAIL" && git config --global user.name "$CI_USER"

exists=$(git show-ref refs/heads/$CI_COMMIT_REF_NAME)
if [ -n "$exists" ]; then
  git branch -D $CI_COMMIT_REF_NAME
  echo "Created branch $CI_COMMIT_REF_NAME"
fi

if [ -d ".git/rebase-apply" ]; then
  rm -fr .git/rebase-apply
  echo "Removed rebase lock file .git/rebase-apply"
fi

echo "Attempt 1 with rebase"

if ! git pull --rebase origin $CI_COMMIT_REF_NAME; then
  echo "Rebase FAILED !"
  echo "Attempt 2 by keeping changes from normal repository"
  echo "/!\ This will override the changes in the private repository !"

  # /!\ theirs will keep the changes in the Pro repository ! ours will keep the changes in the normal one
  if ! git pull -s recursive -X ours --no-edit origin $CI_COMMIT_REF_NAME; then
      echo "Pull FAILED !"
      echo "Attempt 3 by overriding the changes to the private repository"
      echo "/!\ This will create a merge commit"
      echo "/!\ Only overriding dist/, if another directory/file needs to be overridden, it should be added in this script"

      git fetch origin
      git merge --no-commit --no-ff origin/$CI_COMMIT_REF_NAME
      git reset -- dist/
      git status
      git commit -m "Reset dist folder + merge commits"
  fi
fi

# Use this pull command if there are merge conflicts during rebase
# git pull -s recursive -X ours --no-edit origin $CI_COMMIT_REF_NAME
git checkout -b $CI_COMMIT_REF_NAME
echo "Pushing changes to private repository..."

if ! git push --set-upstream origin $CI_COMMIT_REF_NAME;  then
    echo "FAILED to push changes"
    exit 1
fi

echo "Changes pushed"
