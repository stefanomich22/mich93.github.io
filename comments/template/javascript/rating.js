function c5t_rating_highlight(value, path)
{
    for(var i = 1; i <= value; i++)
    {
        document.getElementById('c5t_rating_' + i).src = path + 'highlight.png';
    }
}
function c5t_rating_normal(value, path)
{
    for(var i = 1; i <= value; i++)
    {
        document.getElementById('c5t_rating_' + i).src = path + document.getElementById('c5t_rating_' + i).value + '.png';
        
        if (i <= document.getElementById('c5t_comment_form_rating').value) {
        	document.getElementById('c5t_rating_' + i).src = path + 'full.png';
        } else {
        	document.getElementById('c5t_rating_' + i).src = path + 'empty.png';
        }
    }
}
function c5t_rating_set_value(value, path)
{
    document.getElementById('c5t_comment_form_rating').value = value;
    
    var topValue = parseInt(document.getElementById('c5t_comment_form_rating_top_value').value);
    
    for(var i = 1; i <= topValue; i++)
    {
        document.getElementById('c5t_rating_' + i).src = path + document.getElementById('c5t_rating_' + i).value + '.png';
        
        if (i <= value) {
        	document.getElementById('c5t_rating_' + i).src = path + 'full.png';
        } else {
        	document.getElementById('c5t_rating_' + i).src = path + 'empty.png';
        }
    }
}

