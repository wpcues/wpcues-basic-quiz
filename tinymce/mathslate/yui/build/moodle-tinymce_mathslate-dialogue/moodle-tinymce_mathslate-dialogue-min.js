YUI.add("moodle-tinymce_mathslate-dialogue",function(e,t){M.tinymce_mathslate=M.tinymce_mathslate||{};var n=M.tinymce_mathslate;n.dialogue=null,n.selection=null,n.config=null,n.init=function(t){M.tinymce_mathslate=M.tinymce_mathslate||{},M.tinymce_mathslate.config=t.config||M.tinymce_mathslate.config,M.tinymce_mathslate.help=t.help||M.tinymce_mathslate.help;var n=e.one("#"+t.elementid),r=n.one(".mathslate-container").generateID(),i=new M.tinymce_mathslate.Editor("#"+r,M.tinymce_mathslate.config),s=e.one("#"+r).appendChild(e.Node.create('<button title="'+M.util.get_string("cancel_desc","tinymce_mathslate")+'">'+M.util.get_string("cancel","tinymce_mathslate")+"</button>"));s.on("click",function(){tinyMCEPopup.close()});if(typeof MathJax=="undefined")return;var o=e.one("#"+r).appendChild(e.Node.create('<button title="'+M.util.get_string("display_desc","tinymce_mathslate")+'">'+M.util.get_string("display","tinymce_mathslate")+"</button>")),u=e.one("#"+r).appendChild(e.Node.create('<button title="'+M.util.get_string("inline_desc","tinymce_mathslate")+'">'+M.util.get_string("inline","tinymce_mathslate")+"</button>"));o.on("click",function(){tinyMCEPopup.editor.execCommand("mceInsertContent",!1,"\\["+i.output("tex")+"\\]"),tinyMCEPopup.close()}),u.on("click",function(){tinyMCEPopup.editor.execCommand("mceInsertContent",!1,"\\("+i.output("tex")+"\\)"),tinyMCEPopup.close()}),MathJax.Hub.Queue(["Typeset",MathJax.Hub,i.node.generateID()]),M.tinymce_mathslate.dialogue=n}},"@VERSION@",{requires:["escape","moodle-local_mathslate-editor","moodle-tinymce_mathslate-editor"]});