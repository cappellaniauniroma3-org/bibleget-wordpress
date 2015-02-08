//using localize script to pass in values from options array, access with "obj" which has "options" and "safe_fonts"

// Array Remove - By John Resig (MIT Licensed)
Array.prototype.remove = function(from, to) {
  var rest = this.slice((to || from) + 1 || this.length);
  this.length = from < 0 ? this.length + from : from;
  return this.push.apply(this, rest);
};

console.log("admin.js is successfully loaded");
console.log(obj);
function get_font_index(fontfamily){
	for(font in obj.safe_fonts){
		if(obj.safe_fonts[font]["font-family"] == fontfamily){ return font; }
	}
	return false;
}

jQuery(document).ready(function(){
		
	jQuery(".bibleget-buttonset").buttonset();
	
	var fval = jQuery("#fontfamily_bibleget").val();
	var findex = false;
	if(fval){ findex = get_font_index(fval); }
	ffstr='';
	if(findex){
		ffamily = "'"+obj.safe_fonts[findex]["font-family"]+"',";
		ffallback = (obj.safe_fonts[findex].hasOwnProperty("fallback"))?"'"+obj.safe_fonts[findex].fallback+"',":"";
		fgeneric = obj.safe_fonts[findex]["generic-family"];
		ffstr = ffamily+ffallback+fgeneric;		
	}	
	jQuery("#preview p").css({"font-family":ffstr});
	
	fval = jQuery("#fontsize_bookchapter").val();
	jQuery("#preview .bibleversion").css({"font-size":fval+'px'});
	jQuery("#preview .bookchapter").css({"font-size":fval+'px'});

	fval = jQuery("#fontsize_versenumbers").val();
	jQuery("#preview .bibleversenumber").css({"font-size":fval+'px'});
	
	fval = jQuery("#fontsize_verses").val();
	jQuery("#preview .bibleversetext").css({"font-size":fval+'px'});
	
	fval = jQuery("#fontcolor_bookchapter").val();
	jQuery("#preview .bibleversion").css({"color":fval});
	jQuery("#preview .bookchapter").css({"color":fval});

	fval = jQuery("#fontcolor_versenumbers").val();
	jQuery("#preview .bibleversenumber").css({"color":fval});

	fval = jQuery("#fontcolor_verses").val();
	jQuery("#preview .bibleversetext").css({"color":fval});
	
	if(jQuery("#bkchbld").prop("checked")){ jQuery("#preview .bibleversion,#preview .bookchapter").css({"font-weight":"bold"}); }
	if(jQuery("#bkchitlc").prop("checked")){ jQuery("#preview .bibleversion,#preview .bookchapter").css({"font-style":"italic"}); }
	if(jQuery("#bkchundr").prop("checked")){ jQuery("#preview .bibleversion,#preview .bookchapter").css({"text-decoration":"underline"}); }
	if(jQuery("#bkchstrk").prop("checked")){ jQuery("#preview .bibleversion,#preview .bookchapter").css({"text-decoration":"line-through"}); }
	
	if(jQuery("#vsnmbld").prop("checked")){ jQuery("#preview .bibleversenumber").css({"font-weight":"bold"}); }
	if(jQuery("#vsnmitlc").prop("checked")){ jQuery("#preview .bibleversenumber").css({"font-style":"italic"}); }
	if(jQuery("#vsnmundr").prop("checked")){ jQuery("#preview .bibleversenumber").css({"text-decoration":"underline"}); }
	if(jQuery("#vsnmstrk").prop("checked")){ jQuery("#preview .bibleversenumber").css({"text-decoration":"line-through"}); }
	
	if(jQuery("#vstxbld").prop("checked")){ jQuery("#preview .bibleversetext").css({"font-weight":"bold"}); }
	if(jQuery("#vstxitlc").prop("checked")){ jQuery("#preview .bibleversetext").css({"font-style":"italic"}); }
	if(jQuery("#vstxundr").prop("checked")){ jQuery("#preview .bibleversetext").css({"text-decoration":"underline"}); }
	if(jQuery("#vstxstrk").prop("checked")){ jQuery("#preview .bibleversetext").css({"text-decoration":"line-through"}); }
	
	fval = jQuery("#linespacing_verses").val();
	jQuery("#preview p.verses").css({"line-height":fval+"%"});
	
	jQuery("#fontfamily_bibleget").change(function(){
		fval = jQuery(this).val();
		findex = false;
		if(fval){ findex = get_font_index(fval); }
		ffstr = '';
		if(findex){
			ffamily = "'"+obj.safe_fonts[findex]["font-family"]+"',";
			ffallback = (obj.safe_fonts[findex].hasOwnProperty("fallback"))?"'"+obj.safe_fonts[findex].fallback+"',":"";
			fgeneric = obj.safe_fonts[findex]["generic-family"];
			ffstr = ffamily+ffallback+fgeneric;			
		}
		jQuery("#preview p").css({"font-family":ffstr});
		jQuery(this).css({"font-family":ffstr});
	});
	
	jQuery("#fontsize_verses").change(function(){
		fval = jQuery(this).val();
		jQuery("#preview .bibleversetext").css({"font-size":fval+'px'});
	});
	
	jQuery("#fontsize_versenumbers").change(function(){
		fval = jQuery(this).val();
		jQuery("#preview .bibleversenumber").css({"font-size":fval+'px'});
	});
	
	jQuery("#fontsize_bookchapter").change(function(){
		fval = jQuery(this).val();
		jQuery("#preview .bibleversion").css({"font-size":fval+'px'});
		jQuery("#preview .bookchapter").css({"font-size":fval+'px'});
	});

	jQuery("#fontcolor_verses").change(function(){
		fval = jQuery(this).val();
		jQuery("#preview .bibleversetext").css({"color":fval});
	});
	
	jQuery("#fontcolor_versenumbers").change(function(){
		fval = jQuery(this).val();
		jQuery("#preview .bibleversenumber").css({"color":fval});
	});
	
	jQuery("#fontcolor_bookchapter").change(function(){
		fval = jQuery(this).val();
		jQuery("#preview .bibleversion").css({"color":fval});
		jQuery("#preview .bookchapter").css({"color":fval});
	});

	jQuery(".supersub").click(function(){
		var fval = [];
		if(jQuery("#fontstyle_versenumbers").val()!==""){
			fval = jQuery("#fontstyle_versenumbers").val().split(",");
		}
		if(jQuery(this).prop("checked") && jQuery(this).prop("checked")===true){
			//console.log("now attempting to uncheck the radio input");
			jQuery(".supersub").not(this).prop("checked",false);
			if(jQuery(this).attr("id")==="vsnmsup" ){
				jQuery("#preview .bibleversenumber").removeClass("sub").addClass("sup");
				var index = fval.indexOf("subscript");
				if(index!=-1){ fval.remove(index); }
				fval.push("superscript");
			}
			if(jQuery(this).attr("id")==="vsnmsub" ){
				jQuery("#preview .bibleversenumber").removeClass("sup").addClass("sub");
				var index = fval.indexOf("superscript");
				if(index!=-1){ fval.remove(index); }
				fval.push("subscript");
			}
			
			jQuery(".bibleget-buttonset").buttonset("refresh");
		}
		else{
			jQuery("#preview .bibleversenumber").removeClass("sup sub");
			var index1 = fval.indexOf("subscript");
			if(index1!=-1){ fval.remove(index1); }
			var index2 = fval.indexOf("superscript");
			if(index2!=-1){ fval.remove(index2); }
			//console.log(jQuery(".supersub").not(this).prop("checked"));
		}
		var myval = fval.join(",");
		jQuery("#fontstyle_versenumbers").val(myval);
	});

	jQuery("#bkchbld").change(function(){
		var fval = [];
		if(jQuery("#fontstyle_bookchapter").val()!==""){
			fval = jQuery("#fontstyle_bookchapter").val().split(",");
		}
		if(jQuery(this).prop("checked")){ jQuery("#preview .bibleversion,#preview .bookchapter").css({"font-weight":"bold"}); fval.push("bold"); }
		else{ jQuery("#preview .bibleversion,#preview .bookchapter").css({"font-weight":"normal"}); var index = fval.indexOf("bold"); if(index!=-1){ fval.remove(index); } }
		var myval = fval.join(",");
		jQuery("#fontstyle_bookchapter").val(myval);
	});

	jQuery("#bkchitlc").change(function(){
		var fval = [];
		if(jQuery("#fontstyle_bookchapter").val()!==""){
			fval = jQuery("#fontstyle_bookchapter").val().split(",");
		}
		if(jQuery(this).prop("checked")){ jQuery("#preview .bibleversion,#preview .bookchapter").css({"font-style":"italic"}); fval.push("italic"); }
		else{ jQuery("#preview .bibleversion,#preview .bookchapter").css({"font-style":"normal"}); var index = fval.indexOf("italic"); if(index!=-1){ fval.remove(index); } }
		var myval = fval.join(",");
		jQuery("#fontstyle_bookchapter").val(myval);
	});
	
	jQuery("#bkchundr").change(function(){
		var fval = [];
		if(jQuery("#fontstyle_bookchapter").val()!==""){
			fval = jQuery("#fontstyle_bookchapter").val().split(",");
		}
		if(jQuery(this).prop("checked")){ jQuery("#bkchstrk").prop("checked",false); jQuery(".bibleget-buttonset").buttonset("refresh"); jQuery("#preview .bibleversion,#preview .bookchapter").css({"text-decoration":"underline"}); fval.push("underline"); }
		else{ jQuery("#preview .bibleversion,#preview .bookchapter").css({"text-decoration":"none"}); var index = fval.indexOf("underline"); if(index!=-1){ fval.remove(index); } }
		var myval = fval.join(",");
		jQuery("#fontstyle_bookchapter").val(myval);
	});
	
	jQuery("#bkchstrk").change(function(){
		var fval = [];
		if(jQuery("#fontstyle_bookchapter").val()!==""){
			fval = jQuery("#fontstyle_bookchapter").val().split(",");
		}
		if(jQuery(this).prop("checked")){ jQuery("#bkchundr").prop("checked",false); jQuery(".bibleget-buttonset").buttonset("refresh"); jQuery("#preview .bibleversion,#preview .bookchapter").css({"text-decoration":"line-through"}); fval.push("strikethrough"); }
		else{ jQuery("#preview .bibleversion,#preview .bookchapter").css({"text-decoration":"none"}); var index = fval.indexOf("strikethrough"); if(index!=-1){ fval.remove(index); } }
		var myval = fval.join(",");
		jQuery("#fontstyle_bookchapter").val(myval);
	});
	
	jQuery("#vsnmbld").change(function(){
		var fval = [];
		if(jQuery("#fontstyle_versenumbers").val()!==""){
			fval = jQuery("#fontstyle_versenumbers").val().split(",");
		}
		if(jQuery(this).prop("checked")){ jQuery("#preview .bibleversenumber").css({"font-weight":"bold"}); fval.push("bold"); }
		else{ jQuery("#preview .bibleversenumber").css({"font-weight":"normal"}); var index = fval.indexOf("bold"); if(index!=-1){ fval.remove(index); } }
		var myval = fval.join(",");
		jQuery("#fontstyle_versenumbers").val(myval);
	});

	jQuery("#vsnmitlc").change(function(){
		var fval = [];
		if(jQuery("#fontstyle_versenumbers").val()!==""){
			fval = jQuery("#fontstyle_versenumbers").val().split(",");
		}
		if(jQuery(this).prop("checked")){ jQuery("#preview .bibleversenumber").css({"font-style":"italic"}); fval.push("italic"); }
		else{ jQuery("#preview .bibleversenumber").css({"font-style":"normal"}); var index = fval.indexOf("italic"); if(index!=-1){ fval.remove(index); } }
		var myval = fval.join(",");
		jQuery("#fontstyle_versenumbers").val(myval);
	});

	jQuery("#vsnmundr").change(function(){
		var fval = [];
		if(jQuery("#fontstyle_versenumbers").val()!==""){
			fval = jQuery("#fontstyle_versenumbers").val().split(",");
		}
		if(jQuery(this).prop("checked")){ jQuery("#vsnmstrk").prop("checked",false); jQuery(".bibleget-buttonset").buttonset("refresh"); jQuery("#preview .bibleversenumber").css({"text-decoration":"underline"}); fval.push("underline"); }
		else{ jQuery("#preview .bibleversenumber").css({"text-decoration":"none"}); var index = fval.indexOf("underline"); if(index!=-1){ fval.remove(index); } }
		var myval = fval.join(",");
		jQuery("#fontstyle_versenumbers").val(myval);
	});

	jQuery("#vsnmstrk").change(function(){
		var fval = [];
		if(jQuery("#fontstyle_versenumbers").val()!==""){
			fval = jQuery("#fontstyle_versenumbers").val().split(",");
		}
		if(jQuery(this).prop("checked")){ jQuery("#vsnmundr").prop("checked",false); jQuery(".bibleget-buttonset").buttonset("refresh"); jQuery("#preview .bibleversenumber").css({"text-decoration":"line-through"}); fval.push("strikethrough"); }
		else{ jQuery("#preview .bibleversenumber").css({"text-decoration":"none"}); var index = fval.indexOf("strikethrough"); if(index!=-1){ fval.remove(index); } }
		var myval = fval.join(",");
		jQuery("#fontstyle_versenumbers").val(myval);
	});
	
	//jQuery("#vsnmsup").change(function())
	
	jQuery("#vstxbld").change(function(){
		var fval = [];
		if(jQuery("#fontstyle_verses").val()!==""){
			fval = jQuery("#fontstyle_verses").val().split(",");
		}
		if(jQuery(this).prop("checked")){ jQuery("#preview .bibleversetext").css({"font-weight":"bold"}); fval.push("bold"); }
		else{ jQuery("#preview .bibleversetext").css({"font-weight":"normal"}); var index = fval.indexOf("bold"); if(index!=-1){ fval.remove(index); } }
		var myval = fval.join(",");
		jQuery("#fontstyle_verses").val(myval);
	});

	jQuery("#vstxitlc").change(function(){
		var fval = [];
		if(jQuery("#fontstyle_verses").val()!==""){
			fval = jQuery("#fontstyle_verses").val().split(",");
		}
		if(jQuery(this).prop("checked")){ jQuery("#preview .bibleversetext").css({"font-style":"italic"}); fval.push("italic"); }
		else{ jQuery("#preview .bibleversetext").css({"font-style":"normal"}); var index = fval.indexOf("italic"); if(index!=-1){ fval.remove(index); } }
		var myval = fval.join(",");
		jQuery("#fontstyle_verses").val(myval);
	});

	jQuery("#vstxundr").change(function(){
		var fval = [];
		if(jQuery("#fontstyle_verses").val()!==""){
			fval = jQuery("#fontstyle_verses").val().split(",");
		}
		if(jQuery(this).prop("checked")){ jQuery("#vstxstrk").prop("checked",false); jQuery(".bibleget-buttonset").buttonset("refresh"); jQuery("#preview .bibleversetext").css({"text-decoration":"underline"}); fval.push("underline"); }
		else{ jQuery("#preview .bibleversetext").css({"text-decoration":"none"}); var index = fval.indexOf("underline"); if(index!=-1){ fval.remove(index); } }
		var myval = fval.join(",");
		jQuery("#fontstyle_verses").val(myval);
	});

	jQuery("#vstxstrk").change(function(){
		var fval = [];
		if(jQuery("#fontstyle_verses").val()!==""){
			fval = jQuery("#fontstyle_verses").val().split(",");
		}
		if(jQuery(this).prop("checked")){ jQuery("#vstxundr").prop("checked",false); jQuery(".bibleget-buttonset").buttonset("refresh"); jQuery("#preview .bibleversetext").css({"text-decoration":"line-through"}); fval.push("strikethrough"); }
		else{ jQuery("#preview .bibleversetext").css({"text-decoration":"none"}); var index = fval.indexOf("strikethrough"); if(index!=-1){ fval.remove(index); } }
		var myval = fval.join(",");
		jQuery("#fontstyle_verses").val(myval);
	});
	
	jQuery("#linespacing_verses").change(function(){
		var fval = jQuery(this).val();
		jQuery("#preview p.verses").css({"line-height":fval+"%"});
	});
	
});