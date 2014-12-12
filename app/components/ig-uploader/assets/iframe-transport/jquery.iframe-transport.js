// This [jQuery](https://jquery.com/) plugin implements an `<iframe>`
// [transport](https://api.jquery.com/jQuery.ajax/#extending-ajax) so that
// `$.ajax()` calls support the uploading of files using standard HTML file
// input fields. This is done by switching the exchange from `XMLHttpRequest`
// to a hidden `iframe` element containing a form that is submitted.

// The [source for the plugin](https://github.com/cmlenz/jquery-iframe-transport)
// is available on [Github](https://github.com/) and licensed under the [MIT
// license](https://github.com/cmlenz/jquery-iframe-transport/blob/master/LICENSE).

// ## Usage

// To use this plugin, you simply add an `iframe` option with the value `true`
// to the Ajax settings an `$.ajax()` call, and specify the file fields to
// include in the submssion using the `files` option, which can be a selector,
// jQuery object, or a list of DOM elements containing one or more
// `<input type="file">` elements:

//     $("#myform").submit(function() {
//         $.ajax(this.action, {
//             files: $(":file", this),
//             iframe: true
//         }).complete(function(data) {
//             console.log(data);
//         });
//     });

// The plugin will construct hidden `<iframe>` and `<form>` elements, add the
// file field(s) to that form, submit the form, and process the response.

// If you want to include other form fields in the form submission, include
// them in the `data` option, and set the `processData` option to `false`:

//     $("#myform").submit(function() {
//         $.ajax(this.action, {
//             data: $(":text", this).serializeArray(),
//             files: $(":file", this),
//             iframe: true,
//             processData: false
//         }).complete(function(data) {
//             console.log(data);
//         });
//     });

// ### Response Data Types

// As the transport does not have access to the HTTP headers of the server
// response, it is not as simple to make use of the automatic content type
// detection provided by jQuery as with regular XHR. If you can't set the
// expected response data type (for example because it may vary depending on
// the outcome of processing by the server), you will need to employ a
// workaround on the server side: Send back an HTML document containing just a
// `<textarea>` element with a `data-type` attribute that specifies the MIME
// type, and put the actual payload in the textarea:

//     <textarea data-type="application/json">
//       {"ok": true, "message": "Thanks so much"}
//     </textarea>

// The iframe transport plugin will detect this and pass the value of the
// `data-type` attribute on to jQuery as if it was the "Content-Type" response
// header, thereby enabling the same kind of conversions that jQuery applies
// to regular responses. For the example above you should get a Javascript
// object as the `data` parameter of the `complete` callback, with the
// properties `ok: true` and `message: "Thanks so much"`.

// ### Handling Server Errors

// Another problem with using an `iframe` for file uploads is that it is
// impossible for the javascript code to determine the HTTP status code of the
// servers response. Effectively, all of the calls you make will look like they
// are getting successful responses, and thus invoke the `done()` or
// `complete()` callbacks. You can only communicate problems using the content
// of the response payload. For example, consider using a JSON response such as
// the following to indicate a problem with an uploaded file:

//     <textarea data-type="application/json">
//       {"ok": false, "message": "Please only upload reasonably sized files."}
//     </textarea>

// ### Compatibility

// This plugin has primarily been tested on Safari 5 (or later), Firefox 4 (or
// later), and Internet Explorer (all the way back to version 6). While I
// haven't found any issues with it so far, I'm fairly sure it still doesn't
// work around all the quirks in all different browsers. But the code is still
// pretty simple overall, so you should be able to fix it and contribute a
// patch :)

// ## Annotated Source

(function($,undefined){$.ajaxPrefilter(function(options,origOptions,jqXHR){if(options.iframe){options.originalURL=options.url;return"iframe"}});$.ajaxTransport("iframe",function(options,origOptions,jqXHR){var form=null,iframe=null,name="iframe-"+$.now(),files=$(options.files).filter(":file:enabled"),markers=null,accepts=null;function cleanUp(){files.each(function(i,file){var $file=$(file);$file.data("clone").replaceWith($file)});form.remove();iframe.one("load",function(){iframe.remove()});iframe.attr("src","javascript:false;")}options.dataTypes.shift();options.data=origOptions.data;if(files.length){form=$("<form enctype='multipart/form-data' method='post'></form>").hide().attr({action:options.originalURL,target:name});if(typeof(options.data)==="string"&&options.data.length>0){$.error("data must not be serialized")}$.each(options.data||{},function(name,value){if($.isPlainObject(value)){name=value.name;value=value.value}$("<input type='hidden' />").attr({name:name,value:value}).appendTo(form)});$("<input type='hidden' value='IFrame' name='X-Requested-With' />").appendTo(form);if(options.dataTypes[0]&&options.accepts[options.dataTypes[0]]){accepts=options.accepts[options.dataTypes[0]]+(options.dataTypes[0]!=="*"?", */*; q=0.01":"")}else{accepts=options.accepts["*"]}$("<input type='hidden' name='X-HTTP-Accept'>").attr("value",accepts).appendTo(form);markers=files.after(function(idx){var $this=$(this),$clone=$this.clone().prop("disabled",true);$this.data("clone",$clone);return $clone}).next();files.appendTo(form);return{send:function(headers,completeCallback){iframe=$("<iframe src='javascript:false;' name='"+name+"' id='"+name+"' style='display:none'></iframe>");iframe.one("load",function(){iframe.one("load",function(){var doc=this.contentWindow?this.contentWindow.document:(this.contentDocument?this.contentDocument:this.document),root=doc.documentElement?doc.documentElement:doc.body,textarea=root.getElementsByTagName("textarea")[0],type=textarea&&textarea.getAttribute("data-type")||null,status=textarea&&textarea.getAttribute("data-status")||200,statusText=textarea&&textarea.getAttribute("data-statusText")||"OK",content={html:root.innerHTML,text:type?textarea.value:root?(root.textContent||root.innerText):null};cleanUp();completeCallback(status,statusText,content,type?("Content-Type: "+type):null)});form[0].submit()});$("body").append(form,iframe)},abort:function(){if(iframe!==null){iframe.unbind("load").attr("src","javascript:false;");cleanUp()}}}}})})(jQuery);
