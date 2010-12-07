/*!
 * http://www.JSON.org/json2.js
 * Public Domain
 *
 * JSON.stringify(value, [replacer, [space]])
 * JSON.parse(text, reviver)
 */

if(!this.JSON)this.JSON={};
(function(){function l(c){return c<10?"0"+c:c}function o(c){p.lastIndex=0;return p.test(c)?'"'+c.replace(p,function(f){var b=r[f];return typeof b==="string"?b:"\\u"+("0000"+f.charCodeAt(0).toString(16)).slice(-4)})+'"':'"'+c+'"'}function m(c,f){var b,d,g,j,i=h,e,a=f[c];if(a&&typeof a==="object"&&typeof a.toJSON==="function")a=a.toJSON(c);if(typeof k==="function")a=k.call(f,c,a);switch(typeof a){case "string":return o(a);case "number":return isFinite(a)?String(a):"null";case "boolean":case "null":return String(a);
case "object":if(!a)return"null";h+=n;e=[];if(Object.prototype.toString.apply(a)==="[object Array]"){j=a.length;for(b=0;b<j;b+=1)e[b]=m(b,a)||"null";g=e.length===0?"[]":h?"[\n"+h+e.join(",\n"+h)+"\n"+i+"]":"["+e.join(",")+"]";h=i;return g}if(k&&typeof k==="object"){j=k.length;for(b=0;b<j;b+=1){d=k[b];if(typeof d==="string")if(g=m(d,a))e.push(o(d)+(h?": ":":")+g)}}else for(d in a)if(Object.hasOwnProperty.call(a,d))if(g=m(d,a))e.push(o(d)+(h?": ":":")+g);g=e.length===0?"{}":h?"{\n"+h+e.join(",\n"+h)+
"\n"+i+"}":"{"+e.join(",")+"}";h=i;return g}}if(typeof Date.prototype.toJSON!=="function"){Date.prototype.toJSON=function(){return isFinite(this.valueOf())?this.getUTCFullYear()+"-"+l(this.getUTCMonth()+1)+"-"+l(this.getUTCDate())+"T"+l(this.getUTCHours())+":"+l(this.getUTCMinutes())+":"+l(this.getUTCSeconds())+"Z":null};String.prototype.toJSON=Number.prototype.toJSON=Boolean.prototype.toJSON=function(){return this.valueOf()}}var q=/[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,
p=/[\\\"\x00-\x1f\x7f-\x9f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,h,n,r={"\u0008":"\\b","\t":"\\t","\n":"\\n","\u000c":"\\f","\r":"\\r",'"':'\\"',"\\":"\\\\"},k;if(typeof JSON.stringify!=="function")JSON.stringify=function(c,f,b){var d;n=h="";if(typeof b==="number")for(d=0;d<b;d+=1)n+=" ";else if(typeof b==="string")n=b;if((k=f)&&typeof f!=="function"&&(typeof f!=="object"||typeof f.length!=="number"))throw Error("JSON.stringify");return m("",
{"":c})};if(typeof JSON.parse!=="function")JSON.parse=function(c,f){function b(g,j){var i,e,a=g[j];if(a&&typeof a==="object")for(i in a)if(Object.hasOwnProperty.call(a,i)){e=b(a,i);if(e!==undefined)a[i]=e;else delete a[i]}return f.call(g,j,a)}var d;q.lastIndex=0;if(q.test(c))c=c.replace(q,function(g){return"\\u"+("0000"+g.charCodeAt(0).toString(16)).slice(-4)});if(/^[\],:{}\s]*$/.test(c.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g,"@").replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,
"]").replace(/(?:^|:|,)(?:\s*\[)+/g,""))){d=eval("("+c+")");return typeof f==="function"?b({"":d},""):d}throw new SyntaxError("JSON.parse");}})();
