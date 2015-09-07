jQuery(document).ready(function(){
	jQuery('p.verses span.text').each(function(){
		jQuery(this).contents().first().filter(function(){ 
			return ( 
				this.nodeType === 1 && ( 
					jQuery(this).hasClass('pof') || jQuery(this).hasClass('po') || jQuery(this).hasClass('pol') || jQuery(this).hasClass('pos') || jQuery(this).hasClass('poif') || jQuery(this).hasClass('poi') || jQuery(this).hasClass('poil') 
				) && (
					jQuery(this).parent('span.text').is('span.text:first')
					||
					jQuery(this).parent('span.text').prevAll('span.text:first').contents().last().filter(function(){
						return this.nodeType === 1 && ( jQuery(this).hasClass('pof') || jQuery(this).hasClass('po') || jQuery(this).hasClass('pol') || jQuery(this).hasClass('pos') || jQuery(this).hasClass('poif') || jQuery(this).hasClass('poi') || jQuery(this).hasClass('poil') )
					})
				) 
			) 
		}).css({"display":"inline-block"});
	});		
});


//HERE IS THE LOGIC:
//IF the (first node) following a span.text node is not a text node
//    but it IS an element node with class pof,poif,po,poi,poil...

//AND (the last node within the preceding span.text node IS an element node with class pof,poif,po,poi,poil
//    OR 
//    this is the first span.text node of a chapter)

//THEN change the css display of that (first node) to "inline-block"
