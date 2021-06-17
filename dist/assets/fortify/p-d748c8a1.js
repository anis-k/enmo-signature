import{A as s,e as t,n as a,o as e,E as r,p as i,U as h,q as n,C as c,r as o,V as u,s as b,I as m,t as l,W as p,u as g,v as E,w as k,T as f,x as A,D as x,y as I,z as T,B as d,F as C,G as R,H as w,d as j,J as y,N as F,f as N,g as q,h as v,R as B,l as H,j as S,k as U}from"./p-d1e2b384.js";import{ac as D,j as G,f as K}from"./p-e1655586.js";
/**
 * @license
 * Copyright (c) Peculiar Ventures, LLC.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */class P extends s{constructor(s){super(s,D);const j=this.getAsnExtnValue();switch(this.asn.type){case R:this.value=G.parse(j,w);break;case d:this.value=G.parse(j,C);break;case I:this.value=G.parse(j,T);break;case A:this.value=G.parse(j,x);break;case k:this.value=G.parse(j,f);break;case g:this.value=G.parse(j,E);break;case l:this.value=G.parse(j,p);break;case b:this.value=G.parse(j,m);break;case o:this.value=G.parse(j,u);break;case n:this.value=G.parse(j,c);break;case i:this.value=G.parse(j,h);break;case a:{const s=G.parse(j,e);this.value=s.map(s=>new r(K.serialize(s)));break}default:this.value=t.Convert.ToHex(j)}}getAsnExtnValue(){return this.asn.values[0]}}
/**
 * @license
 * Copyright (c) Peculiar Ventures, LLC.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */class Q extends s{constructor(s){super(j(s),y),this.thumbprints={};const{certificationRequestInfo:t}=this.asn;this.subject=new F(t.subject).toJSON(),this.version=t.version}get publicKey(){const{subjectPublicKey:s,algorithm:t}=this.asn.certificationRequestInfo.subjectPKInfo;let a;return t.algorithm===N&&t.parameters&&(a=K.parse(t.parameters,q)),t.algorithm===v&&(a=K.parse(s,B)),{params:a,value:K.serialize(this.asn.certificationRequestInfo.subjectPKInfo),algorithm:t.algorithm}}get signature(){const{signature:s,signatureAlgorithm:t}=this.asn;return{value:s,algorithm:t.algorithm}}get commonName(){if(!this.subject)return"";for(let s=0;s<this.subject.length;s+=1){const t=this.subject[s];if("CN"===t.shortName||"E"===t.shortName||"O"===t.shortName)return t.value}return""}async getThumbprint(s="SHA-1"){try{const a=await H(s,this.raw);this.thumbprints[s.name||s]=t.Convert.ToHex(a)}catch(a){console.error("Error thumbprint get:",a)}}parseAttributes(){const{certificationRequestInfo:s}=this.asn;s.attributes&&(this.attributes=s.attributes.map(s=>new P(K.serialize(s))))}exportAsBase64(){return t.Convert.ToBase64(this.raw)}exportAsHexFormatted(){return S(t.Convert.ToHex(this.raw))}exportAsPemFormatted(){return`-----BEGIN CERTIFICATE REQUEST-----\n${U(this.exportAsBase64())}\n-----END CERTIFICATE REQUEST-----`}}export{P as A,Q as C}