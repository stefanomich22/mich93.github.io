var c5t = {
		
	/**
	 * Configuration
	 */
	baseUrl: './',
	containerId: 'c5t_container',
	xhrUrl: 'comment.php?xhr=1&',
	templateUrl: 'template/default/',
	jsUrl: 'template/javascript/',
	throbber: 'throbber.gif',
	
	/**
	 * Init xhr
	 */
	init: function()
	{		
		if (c5t_config == undefined) {
			return false;
		}
		if (c5t_config.baseUrl != undefined) {
			this.baseUrl = c5t_config.baseUrl;
		}
		
		if (c5t_config.template != undefined) {
			this.templateUrl = 'template/' + c5t_config.template + '/'; 
		} else {
			c5t_config.template = '';
		}
		
		var fileref = document.createElement("link");
		fileref.setAttribute("rel", "stylesheet");
		fileref.setAttribute("type", "text/css");
		fileref.setAttribute("href", this.baseUrl + this.templateUrl + 'style.css');

		if (typeof fileref != undefined) {
			document.getElementsByTagName("head")[0].appendChild(fileref);
		}
		var script = document.createElement('script');
		script.type = 'text/javascript';
		script.src = this.baseUrl + this.jsUrl + 'rating.js';		
		document.getElementsByTagName("head")[0].appendChild(script);
		
		this.createElementContainers();

		this.createScript(encodeURIComponent('{"method":"loadCommentList", "params":{ "identifier": "' + c5t_config.identifier + '", "template": "' + c5t_config.template + '" }, "id":"callID"}'));
		this.createScript(encodeURIComponent('{"method":"loadCommentForm", "params":{ "identifier": "' + c5t_config.identifier + '", "template": "' + c5t_config.template + '" }, "id":"callID"}'));
	},
	
	/**
	 * Create element containers for form and list
	 */
	createElementContainers: function()
	{
		if (this.e('c5t_comment_list') == undefined) {
			var div = document.createElement('div');
			div.id = 'c5t_comment_list';
			this.e(this.containerId).appendChild(div);
		}
		if (this.e('c5t_comment_form') == undefined) {
			var div = document.createElement('div');
			div.id = 'c5t_comment_form';
			this.e(this.containerId).appendChild(div);
		}
	},
	
	/**
	 * Pagination - Next page
	 */
	page: function(page)
	{
		this.displayThrobber('c5t_pagination_throbber', 'c5t_frontend_pagination');
		this.createScript(encodeURIComponent('{"method":"loadCommentList", "params":{ "identifier": "' + c5t_config.identifier + '", "template": "' + c5t_config.template + '", "page": "' + page + '" }, "id":"callID"}'));
	},
	
	/**
	 * Display comment list
	 */
	displayCommentList: function(json)
	{
		var result = eval('(' + json + ')');
		
		if (result.error != null) {
			return false;
		}
		
		this.hideThrobber('c5t_pagination_throbber');
		this.e('c5t_comment_list').innerHTML = result.result;
		if (window.pageYOffset > 0) {
			this.e('c5t_comment_list').scrollIntoView();
		}
	},
	
	/**
	 * Display comment form
	 */
	displayCommentForm: function(json)
	{
		var result = eval('(' + json + ')');
		
		if (result.error != null) {
			return false;
		}

		this.hideThrobber('c5t_form_throbber');
		this.e('c5t_comment_form').innerHTML = result.result;
		if (window.pageYOffset > 0) {
			this.e('c5t_comment_form').scrollIntoView();
		}
		
	},
	
	/**
	 * Send form data
	 */
	submitCommentForm: function()
	{
		this.displayThrobber('c5t_form_throbber', 'c5t_comment_form_submit');
		
	    var params = '"name" : "' + escape(this.getFormValue('c5t_comment_form_name')) + '"';
	    params += ', "email" : "' + escape(this.getFormValue('c5t_comment_form_email')) + '"';
	    params += ', "homepage" : "' + escape(this.getFormValue('c5t_comment_form_homepage')) + '"';
	    params += ', "title" : "' + escape(this.getFormValue('c5t_comment_form_title')) + '"';
	    params += ', "comment" : "' + escape(this.getFormValue('c5t_comment_form_text')) + '"';
	    params += ', "rating" : "' + parseInt(this.getFormValue('c5t_comment_form_rating')) + '"';
	    
		this.createScript(encodeURIComponent('{"method":"saveCommentData", "params":{ "identifier": "' + c5t_config.identifier + '", "template": "' + c5t_config.template + '", ' + params + ' }, "id":"callID"}'));
	},
	
	/**
	 * Get form values
	 */
	getFormValue: function(id)
	{
		if (this.e(id) == undefined) {
			return '';
		}
		return this.e(id).value;
	},
	
	/**
	 * Create script element
	 */
	createScript: function(json)
	{
	    var d = new Date();
		var script = document.createElement('script');
		script.type = 'text/javascript';
		script.src = this.baseUrl + this.xhrUrl + 'json=' + json + '&t=' + d.getTime();
		
		var container = this.e(this.containerId);
		container.appendChild(script);		
	},
	
	/**
	 * Display throbber
	 */	
	displayThrobber: function(element, parent)
	{
		if (this.e(element) != undefined) {
			this.e(element).style.display = 'inline';
			return true;
		}
		
		var img = document.createElement('img');
		img.src = this.baseUrl + this.templateUrl + 'image/' + this.throbber;
		img.id = element;
		img.className = 'c5t_throbber';
		this.e(parent).appendChild(img);
	},
	
	/**
	 * Hide throbber
	 */
	hideThrobber: function(element)
	{
		if (this.e(element) == undefined) {
			return true;
		}

		this.e(element).style.display = 'none';
	},
	
	/**
	 * Get element
	 */
	e: function(elementId)
	{
		return document.getElementById(elementId);
	}
};

c5t.init();
