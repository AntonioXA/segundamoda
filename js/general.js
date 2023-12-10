function chgImage(a,b,msg){
a.src=b.src;
return chgBar(msg);
}
function chgBar(msg){
 window.status=msg;
 return true;
}
function goURL(go_url){
 window.location.replace(go_url);
}
function nobackbutton()
{
   window.location.hash="no-back-button";
   window.location.hash="Again-No-back-button"
   window.onhashchange=function(){window.location.hash="no-back-button";}   
}