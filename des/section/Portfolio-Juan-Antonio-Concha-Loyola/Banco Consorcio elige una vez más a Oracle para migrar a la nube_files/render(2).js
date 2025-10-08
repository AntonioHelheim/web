/* globals define,console,Promise */
define([
	"jquery",
	"mustache",
	"marked",
	"text!./layout.html",
	"css!./design.css",
	'knockout'
], function ($, Mustache, Marked, templateHtml, css, ko) {
	"use strict";

	// Content Layout constructor function.
	function ContentLayout(params) {
		this.contentItemData = params.contentItemData || {};
		this.scsData = params.scsData;
		this.contentClient = params.contentClient;
		// Backward compatibility for v1 API
		this.contentItemData.fields = this.contentClient.getInfo().contentVersion === 'v1' ? this.contentItemData.data : this.contentItemData.fields;
	}

	// Helper function to format a date field by locale.
	function dateToMDY(date) {
		if (!date) {
			return "";
		}

		var dateObj = new Date(date);

		var options = { year: "numeric", month: "long", day: "numeric" };
		var formattedDate = dateObj.toLocaleDateString("en-US", options);

		return formattedDate;
	}

	function getPostCategories(contentClient, contentId) {
		var channelToken = SCS.siteInfo.properties.channelAccessTokens ? window.SCS.siteInfo.properties.channelAccessTokens[0].value : "";
		return contentClient.graphql({
			'query': `query getSiteData($channelToken: String, $id:ID) {
					 items: getItem(channelToken: $channelToken, id:$id) {
						 ...itemData
					 }
				   }
				   fragment itemData on item {
					 id
					 name
					 type
					 taxonomies {
					   categories {
						 name
						 apiName
					   }
					 }
				   }`,
			'variables': {
				"channelToken": channelToken,
				"id": contentId
			}
		}).then(function (response) {
			var categoriesArr = [];
			let categoryHref = getCategoryHref();
			if (response.data.items && response.data.items.taxonomies.length > 0) {
				response.data.items.taxonomies.forEach(item => {
					if (item.categories.length > 0) {
						item.categories.forEach(categoryItem => {
							categoriesArr.push({ category: categoryItem.name, pageUrl: `${categoryHref}/${categoryItem.apiName}` });

						})
					}
				})
				// Extract the category names from the objects
				var categoryNames = categoriesArr.map(categoryObj => categoryObj.category);

				// Join the category names into a comma-separated string
				var categoryNamesString = categoryNames.join(',');


				// Set the content of the keywords meta tag
				var keywordsMetaTag = document.querySelector('meta[name="keywords"]');
				if (keywordsMetaTag) {
					keywordsMetaTag.setAttribute("content", categoryNamesString);
				}

				// Set the content of the category meta tag
				var categoryMetaTag = document.querySelector('meta[name="category"]');
				if (categoryMetaTag) {
					categoryMetaTag.setAttribute("content", categoryNamesString);
				}
			}
			// data['categories'] = [...categoriesArr];
			return categoriesArr;
		});
	}

	function getCategoryHref() {
		let categoryHref;
		let childrenPages = SCS.structureMap[SCS.navigationRoot].children;

		if (!childrenPages) return; // No pages

		// Find the Category page
		for (let i = 0; i < childrenPages.length; i++) {
			var page = SCS.structureMap[childrenPages[i]];
			if (page.name === 'Category') {
				var linkData = SCSRenderAPI.getPageLinkData(page.id);
				if (linkData && linkData.href) {
					var href = linkData.href;
				}
			}
		}
		if (typeof href !== 'undefined') {
			categoryHref = href.replace(".html", "");
		}
		return categoryHref;
	}

	function appendCacheBusterForAttachments(mdText, cacheBuster) {

		let doc = document.createElement("html");
		doc.innerHTML = mdText;
		let links = doc.getElementsByTagName("a")
		let urls = [];
		let bodyText = mdText;
		for (let i = 0; i < links.length; i++) {
			urls.push(links[i].getAttribute("href"));
		}
		urls.forEach(item => {
			if (item && ((item.includes('orasites') || item.includes('blogs.oracle.com') || item.includes('blogs-stage.oracle.com')) && item.includes('/api/v1.1'))) {
				// bodyText = mdText;
				bodyText = item.includes('?') ? mdText.replace(item, item + "&cb=" + cacheBuster) : mdText.replace(item, item + "?cb=" + cacheBuster);
			}
		})
		return bodyText;
	}
	// Helper function to parse markdown text.
	function parseMarkdown(mdText) {
		if (mdText && /^<!---mde-->\n\r/i.test(mdText)) {
			mdText = mdText.replace("<!---mde-->\n\r", "");

			mdText = Marked(mdText);
		}
		return mdText;
	}
	// Helper function to parse markdown text.
	function updateContentURLs(contentClient, mdText) {
		return new Promise(function (resolve, reject) {
			// parse the string and and any additional parameters to the URLs
			var regDigitalAsset = /\[!--\$\s*CEC_DIGITAL_ASSET\s*--\]\s*(.*?)\s*\[\/!--\$\s*CEC_DIGITAL_ASSET\s*--\]/g
			let cacheBusterKeys = contentClient.getInfo().cacheBuster;
			let cacheBuster = typeof cacheBusterKeys === 'string' ? cacheBusterKeys : (cacheBusterKeys && cacheBusterKeys.contentKey || '');
			let cbAppendedText = appendCacheBusterForAttachments(mdText, cacheBuster);
			var contentIds = [];
			var updatedString = cbAppendedText.replace(regDigitalAsset, function (match, parameters) {
				var params = parameters.split(',');
				var contentId = params[0].trim();
				if (contentId) {
					contentIds.push(contentId);
				} else {
					// Handle invalid or missing contentId
					//console.error("Invalid contentId:", contentId);
					return match;
				}

			});
			if (contentIds.length === 0) {
				// No valid contentIds found
				//console.warn("No valid contentIds found");
				resolve(cbAppendedText); // Resolve with the original cbAppendedText
				return;
			}
			contentClient.getItems({
				'ids': contentIds

			}).then(function (contentItems) {


				var updatedString = cbAppendedText.replace(regDigitalAsset, function (match, parameters) {
					var params = parameters.split(',');
					var contentId = params[0].trim();
					var updatedImgUrl;
					var result = contentItems.items.find(function (entry) {

						return entry.id === contentId;

					});
					if (!result) {
						// Handle invalid contentId
						//console.error("Invalid contentId:", contentId);
						return match; // Keep the original match in updatedString
					}

					var Imagename = result.name;

					var FileType = Imagename.split(".");
					var isImage = FileType[1] === 'jpeg' || FileType[1] === 'jpg' || FileType[1] === 'png';
					if (isImage) {
						if (FileType[1] == 'jpeg' || FileType[1] == 'jpg' || FileType[1] == 'png') {
							updatedImgUrl = contentClient.getRenditionURL({ "id": contentId, "type": 'Medium', "format": 'jpg' });
						} else {
							updatedImgUrl = contentClient.getRenditionURL({ "id": contentId });

						}
						return updatedImgUrl;
					} else {
						// If it's not an image, return the original match (no replacement)
						return match;
					}



				});

				// find all the content URLs and add in rendition value
				return resolve(updatedString);
			});
		});
	}

	function getRefItems(ids) {
		return new Promise(function (resolve, reject) {
			var channelToken = window.SCS.siteInfo.properties.channelAccessTokens ? window.SCS.siteInfo.properties.channelAccessTokens[0].value : "";
			//var authorurl = window.location.origin + '/content/published/api/v1.1/items?q=(id eq "'+ids+'")&channelToken=' + channelToken;
			$.ajax({
				type: 'GET',
				url: window.location.origin + '/content/published/api/v1.1/items?q=(id eq "' + ids + '")&links=none&channelToken=' + channelToken,

				dataType: 'json',
				'headers': {
					'Authorization': self.authorization
				},
				success: function (authors) {
					return resolve(authors);
				}

			});

		});
	}

	function pageFunctions(data) {
		var title = "";
		if (data["title"]) {
			title = data["title"];
		}
		if (title) {
			document.title = data["title"];
			$(".pageName").text(typeof document.title != "undefined" ? document.title : "Error");
		}

	};

	function imgPadding() {
		$(".rc84post img,.rc84post p img").each(function () {
			var prop = $(this).css("float")
			if (prop == 'left') {
				$(this).css({ "padding-right": '10px', "padding-bottom": '10px' })
			}
			if (prop == 'right') {
				$(this).css({ "padding-left": '10px', "padding-bottom": '10px' })
			}
		})
	}

	function codeInitializer(){
		$('#post-id pre').each(function () {
			if($(this).contents().first()[0].nodeType == 3 || $(this).children('code').length == 0){
				$(this).html('<code>' + $(this).html() + '</code>');
			} else if($(this).children('code').length > 0){
				$(this).find('code').html($.parseHTML($(this).find('code').text()))
			} else {
				$(this).html('<code>' + $(this).html() + '</code>');
			}
			$(this).wrap('<div class="ocode"></div>')
			$(this).before('<div class="ocode-bttn" data-success="Copied to Clipboard" data-error="Error: Could not Copy"><div><a href="#copy">Copy code snippet</a></div><div class="ocode-success">Copied to Clipboard</div><div class="ocode-error">Error: Could not Copy</div></div>');
			$(this).after('<textarea readonly="readonly" tabindex="-1"></textarea>');
		});
		
		$("iframe").each(function () {
			let srcAttribute = $(this).attr('src');
			if (srcAttribute) {
				$(this).attr('src', srcAttribute.replace('youtube.com/embed', 'youtube-nocookie.com/embed'));
			}
		});

		if (window.TriggerAdditionalAdobePing) {
        	window.TriggerAdditionalAdobePing()
    	}
		// Get all elements with class 'text-wrap'
		let textWrapElements = document.querySelectorAll('.text-wrap');

		// Loop through each element and add the 'Show more' functionality
		textWrapElements.forEach(function (elem) {
			let elemHeight = elem.offsetHeight;
			if (elemHeight >= 130) {
				let div = document.createElement('div');
				div.classList.add('readmore');
				div.innerText = 'Show more';
				elem.classList.add('text-wrap-short');
				elem.parentNode.appendChild(div);
				readMoreFunc(elem, div);
			}
		});
		/******** This is to initialize the ocode code formatting manually, 
		if the post unable to initialize during compilation ********/
		var OCODE_BLOGS = function ($) {
			"use strict";
			var a, b, c = "ocode-initialized", d = "ocode-show", e = "showTimeout", f = {};
			function buildBlock(c) {
				a = c;
				b = f ? a : a.find("pre > code");
				var f = a.is("code")
					, g = a.find(".ocode-bttn").append(function () {
						return '<div class="ocode-success">' + ($(this).data("success") || "Copied to Clipboard") + '</div><div class="ocode-error">' + ($(this).data("error") || "Error: Could not Copy") + "</div>"
					})
					, h = !f && !a.hasClass("ocode-simple") && $("<textarea/>").attr({
						readonly: "",
						tabindex: -1
					}).appendTo(a);
				b.html(b.html().trim().replace(/\t/g, "  ").replace(/</g, "&lt;").replace(/>/g, "&gt;"));
				if (h) {
					var i = !!navigator.platform && /iPad|iPhone|iPod/.test(navigator.platform);
					h.val(b.text());
					a.on("keyup", function (b) {
						return 9 === b.keyCode && a.addClass("ocode-kb")
					});
					g.on("click", function (a) {
						var b = g.find(".ocode-success")
							, c = g.find(".ocode-error");
						a.preventDefault();
						h[0].select();
						if (i) {
							var f = h[0].contentEditable
								, j = h[0].readOnly
								, k = document.createRange();
							h[0].contentEditable = !0;
							h[0].readOnly = !1;
							h[0].style.left = "-10000px";
							k.selectNodeContents(h[0]);
							var l = window.getSelection();
							l.removeAllRanges();
							l.addRange(k);
							h[0].setSelectionRange(0, 999999);
							h[0].contentEditable = f;
							h[0].readOnly = j;
							return document.execCommand("copy") && b.addClass(d).data(e, setTimeout(function () {
								clearTimeout(b.data(e));
								b.removeClass(d)
							}, 2e3)) || c.addClass(d).data(e, setTimeout(function () {
								clearTimeout(c.data(e));
								c.removeClass(d)
							}, 2e3))
						}
						return document.execCommand("copy") && b.addClass(d).data(e, setTimeout(function () {
							clearTimeout(b.data(e));
							b.removeClass(d)
						}, 2e3)) || c.addClass(d).data(e, setTimeout(function () {
							clearTimeout(c.data(e));
							c.removeClass(d)
						}, 2e3))
					})
				}
			}
			f.initialize = function () {
				$(".ocode").each(function () {
					function onInit() {
						return a.find("pre").html(function (a, b) {
							return b.trim()
						}).end().addClass(c)
					}
					if (!$(this).hasClass(c) && !$(this).find("." + c)[0]) {
						buildBlock($(this));
						return /language-/.test(b[0].classList) && Prism.highlightElement(b[0], !1, onInit) || onInit()
					}
				})
			}
				;
			return f
		}(jQuery);
		OCODE_BLOGS.initialize();
	}

	function loadExternal() {
		
		codeInitializer();

		$("#post-id script").each(function () {
			if ($(this).attr('src') && $(this).attr('src').includes('gist')) {
				$(this).replaceWith("<iframe srcdoc='" + $(this).prop('outerHTML') + "'></iframe>")
				$('iframe').css({
					'width': '100%',
					'border': 'none'
				})
				$('iframe').on('load', function () {
					this.style.height = this.contentWindow.document.body.offsetHeight + 30 + 'px';
				});
			}
		});

		$("#post-id iframe").each(function () {
			let srcAttribute = $(this).attr('src');
			if (srcAttribute && (srcAttribute.includes('embed') || srcAttribute.includes('youtube'))) {
				if (srcAttribute.includes('https')) {
					$(this).attr('src', srcAttribute);
				} else if (srcAttribute.includes('http')) {
					$(this).attr('src', srcAttribute.replace('https'));
				} else {
					$(this).attr('src', 'https://' + srcAttribute);
				}
			}
		})
		/*$('#post-id img').each(function () {
			$(this).attr("loading", "lazy");
			var urlRendition = $(this).attr('src');
			var width = $(this).attr('width') ? `'${$(this).attr("width")}'` : "";
			var height = $(this).attr('height') ? `'${$(this).attr("height")}'` : "";
			var alt = $(this).attr('alt') ? `'${$(this).attr("alt")}'` : "";
			if (urlRendition.includes('Medium') || $(this).attr('src').includes('medium')) { 
				var contentId = urlRendition.split('assets/')[1].split('/')[0];
				var smallUrlRendition = contentClient.getRenditionURL({
					"id": contentId, "type": 'Small', "format": 'jpg'
				});
				var mediumUrlRendition = contentClient.getRenditionURL({
					"id": contentId, "type": 'Medium', "format": 'jpg'
				});
				$(this).replaceWith("<picture> <source media='(min-width:600px)' srcset='" + mediumUrlRendition + "' > <source srcset='" + smallUrlRendition + "'> <img src='"+ mediumUrlRendition + "' alt='" +alt+ "' width='" +width+ "' height='" +height+ "'></picture>"); 
			}
		});*/

	}

	function readMoreFunc(elem, readmoreBtn) {
		readmoreBtn.addEventListener('click', function () {
			if (elem.classList.contains('text-wrap-short')) {
				elem.classList.remove('text-wrap-short');
				readmoreBtn.innerText = "Show less";
			} else {
				elem.classList.add('text-wrap-short');
				readmoreBtn.innerText = "Show more";
			}
		});
	}


	function getRotateClass(index) {
		var classes = ['rw-green-40bg', 'rw-green-blue-bg', 'rw-yellow-40bg'];
		return classes[index % classes.length];
	}

	// Content Layout definition.
	ContentLayout.prototype = {
		// Specify the versions of the Content REST API that are supported by the this Content Layout.
		// The value for contentVersion follows Semantic Versioning syntax.
		// This allows applications that use the content layout to pass the data through in the expected format.
		contentVersion: ">=1.0.0 <2.0.0",

		// Main rendering function:
		// - Updates the data to handle any required additional requests and support both v1.0 and v1.1 Content REST APIs
		// - Expand the Mustache template with the updated data
		// - Appends the expanded template HTML to the parentObj DOM element
		hydrate: function (parentObj) {

			var hydrateData = document.querySelector('.hydrate-container').getAttribute('data-hydrate');

			if (hydrateData) {
				var data = JSON.parse(hydrateData);
				var template,
					content = $.extend({}, this.contentItemData),
					contentClient = this.contentClient;

				pageFunctions(data.contentData);
				codeInitializer();
				imgPadding();

				var moreItems;
				data.metaItems.forEach(function (item) {
					moreItems = data.contentData["author"] || [];
					moreItems.forEach(function (nxtItem) {
						if (nxtItem.id === item.id) {
							var metatags = document.getElementsByTagName("meta");
							for (var i = 0; i < metatags.length; i++) {
								if (metatags[i].name === "author") {
									document.getElementsByTagName("meta")[i].content = item.name;
								}
								// if (metatags[i].name === "blog_name") {
								// 	document.getElementsByTagName("meta")[i].content = item.name;
								// }

							}
						}
					});
				})
			}
		},
		render: function (parentObj) {
			var template,
				content = $.extend({}, this.contentItemData),
				contentClient = this.contentClient,
				fields = this.contentItemData.fields,
				contentType,
				secureContent = false;

			// If used with CECS Sites, Sites will pass in context information via the scsData property.
			if (this.scsData) {
				content = $.extend(content, { "scsData": this.scsData });
				contentType = content.scsData.showPublishedContent === true ? "published" : "draft";
				secureContent = content.scsData.secureContent;
			}

			// Support both v1.0 and v1.1 Content REST API response formats.
			// User-defined fields are passed through the 'data' property in v1.0 and 'fields' property in v1.1.
			var data = !contentClient.getInfo().contentVersion || contentClient.getInfo().contentVersion === "v1" ? content.data : content.fields;

			// Massage the data so that the 'fields' property is always there.
			// The corresponding layout.html template only checks for the ‘fields’ property.
			if (!contentClient.getInfo().contentVersion || contentClient.getInfo().contentVersion === "v1") {
				content["fields"] = content.data;
			}

			//
			// Handle fields specific to this content type.
			//
			let authornames = [];
			let authornamesarr = [];
			var moreItems;

			var referedIds = [];

			//data["body"] = parseMarkdown(contentClient.expandMacros(updateContentURLs(contentClient,data["body"])));
			//data["body"] = contentClient.expandMacros(data["body"]);

			moreItems = data["author"] || [];
			moreItems.forEach(function (nxtItem) {
				// Get the IDs of any referenced assets, we will do an additional query to retrieve these so we can render them as well.
				// If you don’t want to render referenced assets, remove these block.
				referedIds[referedIds.length] = nxtItem.id;
			});
			/*	var featuredImgSettings = function () {
					if (data["featured_image_display_option"] == null) {
						document.getElementsByClassName("rc81photo")[0].style.display = "none";
					} else if (data["featured_image_display_option"][0] == "hidden") {
						document.getElementsByClassName("rc81photo")[0].style.display = "none";
						//var image = document.getElementsByClassName("rc81photo");
						//image[0].style.display = "none";
					} else {
						document.getElementsByClassName("rc81photo")[0].style.display = 'block';
					}
				};

				if (data["featured_image"]) {
					data["featured_image"]["url"] = contentClient.getRenditionURL({ "id": data["featured_image"].id, "type": "Large", "format": "jpg" });
				}
				*/
			var publishDateValue = (data["publish_date"] && data["publish_date"].value) ? data["publish_date"].value : "";

			if (data["publish_date"]) {
				data["publish_date"]["formated"] = dateToMDY(publishDateValue);
			}
			if (data["time_to_read"]) {
				data["time_to_read"] = data["time_to_read"];
			}

			if (data["primary_channel"]) {
				data["primary_channel"] = data["primary_channel"];

			}
			if (data["industry"] || data["product"] || data["audience"]) {
				data.tagsExist = true;
			}
			data.tags = [];
			if (data.industry) {
				data.tags = data.tags.concat(data.industry.map(function (industry, index) {
					var industrySplit = industry.split(',');
					return {
						type: 'industry',
						tagName: industrySplit[0],
						tagUrl: "/tag/" + industrySplit[1],
						rotateClass: getRotateClass(index)
					};
				}));
			}
			if (data["product"]) {
				data.tags = data.tags.concat(data.product.map(function (product, index) {
					var productSplit = product.split(',');
					return {
						type: 'product',
						tagName: productSplit[0],
						tagUrl: "/tag/" + productSplit[1],
						rotateClass: getRotateClass(index)
					};
				}));
			}
			if (data["audience"]) {
				data.tags = data.tags.concat(data.audience.map(function (audience, index) {
					var audienceSplit = audience.split(',');
					return {
						type: 'audience',
						tagName: audienceSplit[0],
						tagUrl: "/tag/" + audienceSplit[1],
						rotateClass: getRotateClass(index)
					};
				}));
			}

			window.SCSMacros = window.SCSMacros || {};
			window.SCSMacros.blogFilter = publishDateValue;
			var self = this;
			var SitesSDK = this.scsData.SitesSDK;
			self.publishDate = ko.observable('');



			self.publishDate.subscribe(function () {
				SitesSDK.publish(SitesSDK.MESSAGE_TYPES.TRIGGER_ACTIONS, {
					'triggerName': 'setPublishDate', // can be any value
					'triggerPayload': [{
						payloadData: window.SCSMacros.blogFilter // update the additional query string to the latest value if the content list has already rendered
					}],
					'actions': ['getPublishDate']
				});
			});

			self.publishDate(publishDateValue);

			moreItems = data["attachments"] || [];
			moreItems.forEach(function (nxtItem) {
				nxtItem["url"] = contentClient.getRenditionURL({ "id": nxtItem.id });
			});




			$(document).ready(function () {


				let domain = window.location.origin;
				if (domain == 'https://blogs-stage.oracle.com') {
					var origin = 'https://blogs-stage.oracle.com/';
					var channelToken = '22775cdb187141a2b6cc6cf49c9fc992';
				} else {
					var origin = 'https://blogs.oracle.com/';
					var channelToken = '842764f99b9a4a06a862ebc785ac9897';
				}
				var cacheKey = SCS.cacheKeys ? SCS.cacheKeys.caas : "";

				let presentpostUrl = window.location.href;
				let postSlug = presentpostUrl.substring(presentpostUrl.lastIndexOf('/') + 1);
				let originalurl = data["translated-pages"];
				// Get the last portion of the URL after the last slash
				if (originalurl != null) {
					var slug = originalurl.substring(originalurl.lastIndexOf('/') + 1);
					// Remove any query parameters or hash fragments from the slug
					slug = slug.split('?')[0].split('#')[0];
				}

				let ajaxUrl;
				if (data["translated-pages"]) {
					ajaxUrl = domain + '/content/published/api/v1.1/items?q=(type eq "Blog-Post" AND (fields.translated-pages eq "' + originalurl + '" OR slug eq "' + slug + '"))&fields=fields.primary_channel,language,slug&links=none&channelToken=' + channelToken + '&cb=' + cacheKey;
				} else {
					ajaxUrl = domain + '/content/published/api/v1.1/items?q=(type eq "Blog-Post" AND (fields.translated-pages eq "' + presentpostUrl + '"))&fields=fields.primary_channel,language,slug&links=none&channelToken=' + channelToken + '&cb=' + cacheKey;
				}
				$.ajax({
					type: 'GET',
					url: ajaxUrl,
					dataType: 'json',
					headers: {
						'Authorization': self.authorization
					}
				}).done(function (blogs) {

					// Extract all slug and language values from the response
					let itemSlugs = blogs.items.map(item => item.slug);
					let itemLanguages = blogs.items.map(item => item.language);
					let itemPrimarychannel = blogs.items.map(item => item.fields.primary_channel);
					if (postSlug) {
						let index = itemSlugs.indexOf(postSlug);
						if (index !== -1) {
							itemSlugs.splice(index, 1);
							itemLanguages.splice(index, 1);
							itemPrimarychannel.splice(index, 1);
						}
					}

					// Create and add <link> tags dynamically
					for (let i = 0; i < itemSlugs.length; i++) {
						if (itemPrimarychannel[i] !== "undefined") {
							let linkTag = document.createElement('link');
							linkTag.rel = 'alternate';
							linkTag.hreflang = itemLanguages[i];
							linkTag.href = origin + itemPrimarychannel[i] + '/post/' + itemSlugs[i];

							// Add the <link> tag to the document's head
							document.head.appendChild(linkTag);
						}
					}
				}).fail(function (jqXHR, textStatus, errorThrown) {
					// Display error message to users in all modes except live mode if site has no taxonomies
					console.error("Unable to get recentposts ", jqXHR, textStatus, errorThrown);
				});

				data.productTags = [];
				data.audienceTags = [];

				if (data["product"]) {
					data.productTags = data.productTags.concat(data.product.map(function (product, index) {
						var productSplit = product.split(',');
						return {
							type: 'product',
							tagName: productSplit[0]

						};
					}));

					var productMetaTags = document.querySelectorAll('meta[name="product"]');

					productMetaTags.forEach(function (metaTag) {
						if (data.productTags.length === 1) {
							metaTag.content = data.productTags[0].tagName;
						} else {
							metaTag.content = data.productTags.map(function (tag) {
								return tag.tagName;
							}).join(', ');
						}
					});
				}

				if (data["audience"]) {
					data.audienceTags = data.audienceTags.concat(data.audience.map(function (audience, index) {
						var audienceSplit = audience.split(',');
						return {
							type: 'audience',
							tagName: audienceSplit[0]

						};
					}));

					var audienceMetaTags = document.querySelectorAll('meta[name="audience"]');

					audienceMetaTags.forEach(function (metaTag) {
						if (data.audienceTags.length === 1) {
							metaTag.content = data.audienceTags[0].tagName;
						} else {
							metaTag.content = data.audienceTags.map(function (tag) {
								return tag.tagName;
							}).join(', ');
						}
					});
				}
				if (data["canonical_url"]) {
					var linktags = document.getElementsByTagName("link");
					for (var i = 0; i < linktags.length; i++) {
						if (linktags[i].rel === "canonical") {
							document.getElementsByTagName("link")[i].href = data["canonical_url"];
						}
					}
				}
				else {
					var linktags = document.getElementsByTagName("link");
					for (var i = 0; i < linktags.length; i++) {
						if (linktags[i].rel === "canonical") {
							document.getElementsByTagName("link")[i].remove();
						}
					}
				}
				//Metatags to postpage
				var hostname = window.location.hostname;
				var metatags = document.getElementsByTagName("meta");

				var publishDateValue = (data["publish_date"] && data["publish_date"].value) ? data["publish_date"].value : "";

				for (var i = 0; i < metatags.length; i++) {
					/*if (metatags[i].name === "industry") {

						document.getElementsByTagName("meta")[i].content = data["industry"] ? data["industry"] : "";

					}
					if (metatags[i].name === "product") {
						document.getElementsByTagName("meta")[i].content = data["product"] ? data["product"] : "";
					}
					if (metatags[i].name === "audience") {
						document.getElementsByTagName("meta")[i].content = data["audience"] ? data["audience"] : "";
					}*/
					if (metatags[i].name === "description") {
						if (data["meta_description"]) {
							document.getElementsByTagName("meta")[i].content = data["meta_description"];
						} else {
							document.getElementsByTagName("meta")[i].content = data["desc"];
						}
					}
					if (metatags[i].name === "title") {
						if (data["meta_title"]) {
							document.getElementsByTagName("meta")[i].content = data["meta_title"];
						} else {
							document.getElementsByTagName("meta")[i].content = data["title"];
						}
					}
					if (metatags[i].name === "robots") {
						if (SCS && SCS['siteId'] === "Blogs-Home") {
							document.getElementsByTagName("meta")[i].content = "noindex nofollow";
						} else {
							document.getElementsByTagName("meta")[i].content = "index, follow";
						}
					}
					if (metatags[i].name === "host_name") {
						document.getElementsByTagName("meta")[i].content = 'https://' + hostname;
					}
					if (metatags[i].name === "publish_date") {
						document.getElementsByTagName("meta")[i].content = dateToMDY(publishDateValue);
					}
					if (typeof SCSRenderAPI.getCustomSiteProperty('GoogleSiteVerification') !== "undefined") {
						if (SCSRenderAPI.getCustomSiteProperty('GoogleSiteVerification') != '') {
							var GoogleSiteVerification = SCSRenderAPI.getCustomSiteProperty('GoogleSiteVerification');
							if (metatags[i].name === "google-site-verification") {
								document.getElementsByTagName("meta")[i].content = GoogleSiteVerification;
							}

						} else {
							//console.log("google1" + GoogleSiteVerification);
							//document.getElementsByTagName("meta")[i].remove();

						}
					}
					else {
						//console.log("google2" + GoogleSiteVerification);
						if (metatags[i].name === "google-site-verification") {
							document.getElementsByTagName("meta")[i].remove();
						}
					}

				}
				//og tags
				var posturl = window.location.href;
				if (data["og_image"]) {
					data["og_image"]["url"] = contentClient.getRenditionURL({
						"id": data["og_image"].id, "type": 'Medium', "format": 'jpg'
					});
					document.querySelector('meta[property="og:image"]').setAttribute("content", data["og_image"]["url"]);
					document.querySelector('meta[name="twitter:image"]').setAttribute("content", data["og_image"]["url"]);
				} else if (data["featured_image"]) {
					data["featured_image"]["url"] = contentClient.getRenditionURL({
						"id": data["featured_image"].id, "type": 'Medium', "format": 'jpg'
					});
					document.querySelector('meta[property="og:image"]').setAttribute("content", data["featured_image"]["url"]);
					document.querySelector('meta[name="twitter:image"]').setAttribute("content", data["featured_image"]["url"]);
				}
				if (data["og_title"]) {
					document.querySelector('meta[property="og:title"]').setAttribute("content", data["og_title"]);
					document.querySelector('meta[name="twitter:title"]').setAttribute("content", data["og_title"]);
				} else {
					document.querySelector('meta[property="og:title"]').setAttribute("content", data["title"]);
					document.querySelector('meta[name="twitter:title"]').setAttribute("content", data["title"]);
				}
				if (data["og_description"]) {
					document.querySelector('meta[property="og:description"]').setAttribute("content", data["og_description"]);
					document.querySelector('meta[name="twitter:description"]').setAttribute("content", data["og_description"]);
				} else {
					document.querySelector('meta[property="og:description"]').setAttribute("content", data["desc"]);
					document.querySelector('meta[name="twitter:description"]').setAttribute("content", data["desc"]);
				}
				document.querySelector('meta[property="og:url"]').setAttribute("content", posturl);

			});





			// If any referenced items exist, fetch them before we render.
			Promise.all([
				updateContentURLs(contentClient, data["body"]),
				getRefItems(referedIds),
				getPostCategories(contentClient, content.id)
			]).then(function (results) {

				data["body"] = parseMarkdown(contentClient.expandMacros(results[0]));
				data['categories'] = results[2] && results[2].length > 0 ? [...results[2]].sort(function (a, b) { return a.category.toLowerCase() < b.category.toLowerCase() ? -1 : 1 }) : [];
				var items = results[1] && results[1].items || [];
				//var author = results && results.items && results.items[0] || [];
				//author.fields = contentClient.getInfo().contentVersion === 'v1' ? author.data : author.fields;

				//content.author_id = author.id;

				// Support v1 bulk query.
				if (!Array.isArray(items)) {
					var newItems = [];
					Object.keys(items).forEach(function (key) {
						newItems.push(items[key]);
					});
					items = newItems;
				}
				// Store the retrieved referenced items in the data used by the template.
				items.forEach(function (item) {
					// Massage the data so that the 'fields' property is always there.
					// The corresponding layout.html template only checks for the ‘fields’ property.
					if (!contentClient.getInfo().contentVersion || contentClient.getInfo().contentVersion === "v1") {
						item["fields"] = item.data;
					}

					moreItems = data["author"] || [];

					// Retrieve the reference item from the query result.
					moreItems.forEach(function (nxtItem) {
						if (nxtItem.id === item.id) {
							if (window.location.origin !== "https://www.ateam-oracle.com") {
								item.fields.detail_url = window.location.origin + '/authors' + '/' + item.slug;
							}
							else {
								item.fields.detail_url = 'https://blogs.oracle.com/authors' + '/' + item.slug;
							}
							const Default_Image = "/assets/img/ui_defaultuserimage.jpg";
							var default_profile_img = SCSRenderAPI.getThemeUrlPrefix() + Default_Image;
							//item.fields.profile_image_url = contentClient.getRenditionURL({"id":item.fields.profile_image.id,"type": "Thumbnail", "format": "jpg"});
							if (item.fields.profile_image != null) {
								item.fields.profile_image_url = contentClient.getRenditionURL({ "id": item.fields.profile_image.id, "type": "Thumbnail", "format": "jpg" });

							} else {
								item.fields.profile_image_url = default_profile_img;
							}
							if (item.fields.job_title != null) {
								item.fields.Author_job_title = item.fields.job_title

							}

							var metatags = document.getElementsByTagName("meta");
							for (var i = 0; i < metatags.length; i++) {
								authornames.push(item.name);
								authornamesarr = [...new Set(authornames)];

								if (metatags[i].name === "blog_name") {
									if (typeof SCSRenderAPI.getCustomSiteProperty('BlogNavTitle') !== "undefined") {
										if (SCSRenderAPI.getCustomSiteProperty('BlogNavTitle') != '') {
											var blogname = SCSRenderAPI.getCustomSiteProperty('BlogNavTitle');
											document.getElementsByTagName("meta")[i].content = blogname;


										}
									}

								}
							}
							nxtItem["contentItem"] = item;
							document.querySelector('meta[name="author"]').setAttribute("content", authornamesarr.join());
						}


					});

				});

				try {
					// Use Mustache to expand the HTML template with the data.
					template = Mustache.render(templateHtml, content);

					// Insert the expanded template into the passed in container.
					if (template) {
						$(parentObj).append(template);
						$("#category-id").appendTo('#categories');
						//getFontsize();
						loadExternal();
						//featuredImgSettings();
						imgPadding();
						pageFunctions(content.fields);
					}

				} catch (e) {
					console.error(e.stack);
				}

			});
		}
	};

	return ContentLayout;
});
