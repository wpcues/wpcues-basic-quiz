YUI.add("moodle-tinymce_mathslate-textool",function(e,t){M.tinymce_mathslate=M.tinymce_mathslate||{};var n=M&&M.tinymce_mathslate||{};n.TeXTool=function(t,n){var r=e.Node.create('<input type="text">'),i=e.Node.create("<span>\\[ \\]</span>");n&&i.on("click",function(){n(i.json)}),e.one(t).appendChild(r),e.one(t).appendChild(i),r.focus();var s=new e.DD.Drag({node:i});s.on("drag:end",function(){this.get("node").setStyle("top","0"),this.get("node").setStyle("left","0")}),i.toMathML=function(e){var t,n=MathJax.Hub.getAllJax(this.generateID())[0];try{t=n.root.toMathML("")}catch(r){if(!r.restart)throw r;return MathJax.Callback.After([toMathML,n,e],r.restart)}MathJax.Callback(e)(t)},r.on("change",function(){function u(n){n=n.replace(/.*<math xmlns=\"http:\/\/www.w3.org\/1998\/Math\/MathML\" display=\"block\">\s*/,"[").replace(/\s*<\/math.*/,"]");if(/<mtext mathcolor="red">/.test(n)||/<merror/.test(n)){console.log(n),o=[""],i.json=null,MathJax.Hub.Queue(["Text",t,""]);return}o=n.replace("<mrow>",'["mrow",{"tex": "'+r.getDOMNode().value+'"},['),o=o.replace(/ class="[^"]*"/g,""),["mrow","mfrac","msub","msup","msubsup","munder","mover","munderover","msqrt","mroot","mtable","mtr","mtd"].forEach(function(e){o=o.replace(new RegExp("<"+e+">","g"),'["'+e+'",{},[').replace(new RegExp("</"+e+">","g"),"]],")}),o=o.replace(/<mo stretchy="false">/g,'["mo",{"stretchy": "false"},"'),["mo","mi","mn","mtext"].forEach(function(e){o=o.replace(new RegExp("<"+e+">","g"),'["'+e+'",{},"').replace(new RegExp("</"+e+">","g"),'"],')}),o=o.replace(/<mi mathvariant="([a-z]*)">/g,'["mi",{"mathvariant": "$1"},"'),o=o.replace(/<mtable rowspacing="([^"]*)" columnspacing="([^"]*)">/g,'["mtable", {"rowspacing":"$1","columnspacing":"$2"},['),o=o.replace(/<mstyle displaystyle="true">/g,'["mstyle",{"displaystyle": "true"},[').replace(/<\/mstyle>/g,"]]"),o=o.replace(/,\s*\]/g,"]"),o=o.replace(/\\/g,"\\\\"),o=o.replace(/<!--.*?-->/g,""),o=o.replace(/&#x([\dA-F]{4});/g,"\\u$1"),o='["mrow", {"tex":["'+r.getDOMNode().value.replace(/\\/g,"\\\\")+'"]},'+o+"]",console.log(o),i.json=o;if(/<[a-z]/.test(o)){console.log(o),o=[""],i.json=null;return}o=[e.JSON.parse(o)]}var t=MathJax.Hub.getAllJax(i.generateID())[0];if(!t)return;var o;MathJax.Hub.Queue(["Text",t,this.getDOMNode().value]),MathJax.Hub.Queue(["Typeset",MathJax.Hub,i.generateID()]),MathJax.Hub.Queue(["toMathML",i,u]),MathJax.Hub.Queue(function(){s.set("data",i.json),n(i.json)})})}},"@VERSION@",{requires:["dd-drag","dd-proxy","dd-drop","event","json"]});