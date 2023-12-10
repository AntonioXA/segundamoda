function appEmbed(mylink, windowname, width, height)
{
	window.open(mylink, windowname, 'width=' + width + ',height=' + height + ',scrollbars=yes,status=no,resizable=yes,toolbar=no');
        return true;
}