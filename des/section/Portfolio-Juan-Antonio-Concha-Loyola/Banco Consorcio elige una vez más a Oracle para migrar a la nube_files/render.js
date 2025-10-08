/* globals define */
define(['knockout', 'jquery', 'css!./styles/design.css', 'text!./render.html'], function (ko, $, css, template) {
	'use strict';

	// ----------------------------------------------
	// Define a Knockout ViewModel for your template
	// ----------------------------------------------
	var SampleComponentViewModel = function (args) {
		var self = this,
			SitesSDK = args.SitesSDK;

		// store the args
		self.mode = args.viewMode;
		self.id = args.id;

		// create the observables
		self.taxonomyId = ko.observable();
		self.categories = ko.observableArray([]);
		self.relatedContent = ko.observableArray([]);
		self.compiledSite = ko.observable(false)

		// To get list of categories for a taxonomy
		self.getCategoriesFromTaxonomy = function (taxonomy) {
			var categories = [];
			// Get the Categories assigned to the currently selected Taxonomy
			if (taxonomy !== undefined) {
				var channelToken = window.SCS.siteInfo.properties.channelAccessTokens ? window.SCS.siteInfo.properties.channelAccessTokens[0].value : "";
				if (!channelToken)
					self.displayError(true);
				$.ajax({
					type: 'GET',
					url: window.location.origin + '/content/published/api/v1.1/taxonomies/' + taxonomy + '/categories?orderBy=position:asc&fields=name,id,apiName,parent,description&links=none&channelToken=' + channelToken + '&cb=' + SCSCacheKeys.caas,
					dataType: 'json',
					'headers': {
						'Authorization': self.authorization
					}
				}).done(function (data) {
					var childrenPages = SCS.structureMap[SCS.navigationRoot].children;
					if (!childrenPages) return; // No pages
					// Find the Category page
					for (var i = 0; i < childrenPages.length; i++) {
						var page = SCS.structureMap[childrenPages[i]];
						if (page.name === 'Category') {
							var linkData = SCSRenderAPI.getPageLinkData(page.id);
							if (linkData && linkData.href) {
								var href = linkData.href;
								href = href.replace(".html", "");
							}
						}
					}
					//var parentCategories = data.items.filter(categ => !categ.parent);
					var parentCategories = data.items.filter((a, i) => !a.parent && data.items.findIndex((s) => a.id === s.id) === i);
					parentCategories.forEach(parentItem => {
						parentItem.subCategories = [{ ...parentItem, href: href.concat("/", parentItem.apiName) }];
						data.items.forEach(item => {
							item.href = href.concat("/", item.apiName);
							if (item.parent) {
								if (parentItem.id === item.parent.id) {

									parentItem.subCategories.push(item);
								}
							};
						})
					});

					var metatags = document.getElementsByTagName("meta");
					var postcategories = [];
					if (parentCategories.length !== 0) {
						parentCategories.forEach((Item, i) => {
							postcategories[i] = (Item.name);
							
						});

					}
					// for (var i = 0; i < metatags.length; i++) {
					// 	if (metatags[i].name === "category" && postcategories.length !== 0) {
					// 		document.getElementsByTagName("meta")[i].content = postcategories.join();
					// 	}
					// 	if (metatags[i].name === "keywords" && postcategories.length !== 0) {
					// 		document.getElementsByTagName("meta")[i].content = postcategories.join();
					// 	}

					// }

					self.categories(parentCategories);

					$('.hasMenu').keydown(function (e) {
						if (e.keyCode == 9) {
							showSubCategories(this);
						}
					});
					$(".mainMenu").keydown(function (e) {
						if (e.shiftKey && e.keyCode == 9) {
							var hasMenuElement = $(this).prev('li').find('a').hasClass('hasMenu');
							if (hasMenuElement) {
								e.preventDefault();
								showSubCategories($(this).prev('li').find('a.hasMenu'));
								$(".sub-categories.active li:last-child a").focus();
							}
						}
					})

				}).fail(function (jqXHR, textStatus, errorThrown) {
					// Display error message to users in all modes except live mode if site has no taxonomies
					//console.error("Unable to get categories ", jqXHR, textStatus, errorThrown);
					var metatags = document.getElementsByTagName("meta");
					for (var i = 0; i < metatags.length; i++) {

						if (metatags[i].name === "category" || metatags[i].name === "keywords") {
							document.getElementsByTagName("meta")[i].content = "Oracle Blogs";
						}

					}
				});
			}
		};


		// 
		// Handle property changes
		//
		self.updateCustomSettingsData = $.proxy(function (customData) {
			if (customData) {
				self.taxonomyId(customData && customData.taxonomyId);
				var hydrateData = document.querySelector('.hydrated-container') ? document.querySelector('.hydrated-container').getAttribute('data-hydrated') : "";
				if (hydrateData) {
					var data = JSON.parse(hydrateData);
					if(window.location.hostname === 'www.ateam-oracle.com'){
						document.querySelector('a.blog-name').href='/';
					} else if(window.location.pathname.split('/')[1] === 'site'){
						document.querySelector('a.blog-name').href='/site/'+data.siteName+'/';
					}else {
						document.querySelector('a.blog-name').href='/'+data.siteName+'/';
					}
					self.compiledSite(data.compiledSite);
				}
				if(!self.compiledSite()){
					self.getCategoriesFromTaxonomy(self.taxonomyId());
				}
			}
		}, self);

		self.updateSettings = function (settings) {
			if (settings.property === 'customSettingsData') {
				self.updateCustomSettingsData(settings.value);
			}
		};
		$(document).ready(function () {
			var relatedContent = [];
			for (let i = 1; i <= 20; i++) {
				if (typeof SCSRenderAPI.getCustomSiteProperty('MenuLink' + i) !== "undefined" && SCSRenderAPI.getCustomSiteProperty('MenuLink' + i)) {
					var linkArr = SCSRenderAPI.getCustomSiteProperty('MenuLink' + i).split(',')
					relatedContent.push({ name: linkArr[0], href: linkArr[1] });
				} else {
					break;
				}
			}
			self.relatedContent(relatedContent);
			var metatags = document.getElementsByTagName("meta");
			for (var i = 0; i < metatags.length; i++) {
				if (metatags[i].name === "country") {
					if (typeof SCSRenderAPI.getCustomSiteProperty('Country') !== "undefined" && SCSRenderAPI.getCustomSiteProperty('Country') != '') {
						var Country = SCSRenderAPI.getCustomSiteProperty('Country');
						document.getElementsByTagName("meta")[i].content = Country;
					} else {
						document.getElementsByTagName("meta")[i].content = "US";
					}
				}
				if (metatags[i].name === "Language") {
					if (typeof SCSRenderAPI.getCustomSiteProperty('Language') !== "undefined" && SCSRenderAPI.getCustomSiteProperty('Language') != '') {
						var Language = SCSRenderAPI.getCustomSiteProperty('Language');
						document.getElementsByTagName("meta")[i].content = Language;
					} else {
						document.getElementsByTagName("meta")[i].content = "en-US";
					}

				}
				if (metatags[i].name === "robots") {
					if (SCS.siteId === "blogs-compiled-test-site" || SCS.siteId === 'blogs-dynamic-test-site') {
					  var Robots = SCS.siteId;
					  document.getElementsByTagName("meta")[i].content = "noindex nofollow";
					} else {
					  document.getElementsByTagName("meta")[i].content = "index follow";
					}
				}
			}

			
		});


		function showCategories() {
			// $('.categ-active').focus();
			$('.categ-menu').removeClass('categ-active');
			$('.mainMenu').show();
			$('.back-btn').removeClass('show');
			$('.categories-text').show();
			// $('.sub-categories').hide();
			// $('.hasMenu').show();
			$('.back-btn').hide();
			$('.sub-categories').removeClass('active');
			$('.hasMenu').addClass('active');
		}
		function showSubCategories(eventTarget) {
			$(eventTarget).addClass('categ-active');
			$('.mainMenu').not(eventTarget).each(function () {
				$(this).hide();
			});
			$('.sub-categories.active li:first-child a').focus();
			$('.categories-text').hide();
			$('.categ-active').next('.back-btn').addClass('show');
			$(eventTarget).parent('.mainMenu').show();
			$(eventTarget).siblings('.sub-categories').addClass('active');
			$(eventTarget).removeClass('active');
			$('.back-btn').show();
			$(".sub-categories.active li:last-child a").keydown(function (e) {
				if (e.keyCode == 9) {
					showCategories();
					$('.hasMenu').addClass('active');
				}
				// $('.categ-active').parent('.mainMenu').next('.mainMenu .hasMenu').focus();

			});

		}

		$(document).on('click', '.hasMenu', function (e) {
			e.preventDefault();
			showSubCategories(this);
		});
		$(document).on('click', '.back-btn', function (e) {
			showCategories();
			// $('.categ-menu.active').parent('.mainMenu').next('li').find('a').focus();
		});
		$('.back-btn').keydown(function (e) {
			if (e.keyCode == 9) {
				showCategories();
				$('.categ-menu.active').parent('.mainMenu').next('li').find('a').focus();

			}
		});

		// listen for settings update
		SitesSDK.subscribe(SitesSDK.MESSAGE_TYPES.SETTINGS_UPDATED, $.proxy(self.updateSettings, self));


		//
		// Initialize the componentLayout & customSettingsData values
		//
		SitesSDK.getProperty('customSettingsData', self.updateCustomSettingsData);
	};

	$(document).ready(function () {
		$("#category-id").appendTo('#categories');
		if (SCS && SCS['siteId'] === "Blogs-Home") {
			$('#contentSearch').hide();
			$('#customSearch').show();
		} else {
			$('#customSearch').hide();
			$('#contentSearch').show();
		}
		var SubscribeHeading,
			SubscribeLink,
			SubscribeSearchBlogs,
			SubscribeButtonTop,
			SubscribeButtonFooter;
		var pathArray = window.location.pathname.split('/');
		
		if (SCSRenderAPI.getCustomSiteProperty('SocialShareicons') == "yes") {
			var socialShareWrappers = document.querySelectorAll('.social-share-wrapper');
			  socialShareWrappers.forEach(function(wrapper) {
			    wrapper.style.display = 'flex';
			 });
			if (typeof SCSRenderAPI.getCustomSiteProperty('FacebookURL') !== "undefined" ) {
				if (SCSRenderAPI.getCustomSiteProperty('FacebookURL') !== '' ){
					//SubscribeLink = SCSRenderAPI.getCustomSiteProperty('EmailSubscriptionURL');
					document.getElementById("facebook-url").href = SCSRenderAPI.getCustomSiteProperty('FacebookURL');
					document.getElementById("facebook-url").style.display = 'block';
				}
			}	
			if (typeof SCSRenderAPI.getCustomSiteProperty('TwitterURL') !== "undefined" ) {
				if (SCSRenderAPI.getCustomSiteProperty('TwitterURL') !== '' ){
					//SubscribeLink = SCSRenderAPI.getCustomSiteProperty('EmailSubscriptionURL');
					document.getElementById("twitter-url").href = SCSRenderAPI.getCustomSiteProperty('TwitterURL');
					document.getElementById("twitter-url").style.display = 'block';
				}
			}
			if (typeof SCSRenderAPI.getCustomSiteProperty('LinkedinURL') !== "undefined" ) {
				if (SCSRenderAPI.getCustomSiteProperty('LinkedinURL') !== '' ){
					//SubscribeLink = SCSRenderAPI.getCustomSiteProperty('EmailSubscriptionURL');
					document.getElementById("linkedin-url").href = SCSRenderAPI.getCustomSiteProperty('LinkedinURL');
					document.getElementById("linkedin-url").style.display = 'block';
				}
			}
			if (typeof SCSRenderAPI.getCustomSiteProperty('YoutubeURL') !== "undefined" ) {
				if (SCSRenderAPI.getCustomSiteProperty('YoutubeURL') !== '' ){
					//SubscribeLink = SCSRenderAPI.getCustomSiteProperty('EmailSubscriptionURL');
					document.getElementById("youtube-url").href = SCSRenderAPI.getCustomSiteProperty('YoutubeURL');
					document.getElementById("youtube-url").style.display = 'block';
				}
			}
			if (typeof SCSRenderAPI.getCustomSiteProperty('InstagramURL') !== "undefined" ) {
				if (SCSRenderAPI.getCustomSiteProperty('InstagramURL') !== '' ){
					//SubscribeLink = SCSRenderAPI.getCustomSiteProperty('EmailSubscriptionURL');
					document.getElementById("instagram-url").href = SCSRenderAPI.getCustomSiteProperty('InstagramURL');
					document.getElementById("instagram-url").style.display = 'block';
				}
			}	
		}else{
			 	var socialShareWrappers = document.querySelectorAll('.social-share-wrapper');
				socialShareWrappers.forEach(function(wrapper) {
					wrapper.style.display = 'flex';
				});
 		}		
		if (typeof SCSRenderAPI.getCustomSiteProperty('BlogNavTitle') !== "undefined") {
			if (SCSRenderAPI.getCustomSiteProperty('BlogNavTitle') != '') {
				document.querySelectorAll('.blog-name').forEach(item => item.innerHTML = SCSRenderAPI.getCustomSiteProperty('BlogNavTitle'));


				if (pathArray[1] === "site") {
					document.querySelector('.blog-name').href = `${window.location.origin}/site/${pathArray[2]}/`
				} else if (pathArray[1] === "post" || pathArray[1] === "category" || pathArray[1] === "authors" || pathArray[1] === "search.html" || pathArray[1] === "tag" || window.location.pathname == "/" || !window.location.pathname) {
					document.querySelector('.blog-name').href = `${window.location.origin}/`
				} else {
					document.querySelector('.blog-name').href = `${window.location.origin}/${pathArray[1]}/`
				}

			} else {
				console.log("blog nav title is blank.");
			}
		}
		if (typeof SCSRenderAPI.getCustomSiteProperty('Subtitle') !== "undefined" && SCSRenderAPI.getCustomSiteProperty('Subtitle') != '') {
			if (document.querySelector(".rh03subtitle p")) {
				document.querySelector(".rh03subtitle p").innerHTML = SCSRenderAPI.getCustomSiteProperty('Subtitle');
			}
		} else {
			console.log("blog nav subtitle is blank.");
		}

		// if (typeof SCSRenderAPI.getCustomSiteProperty('BannerDescription') !== "undefined" && SCSRenderAPI.getCustomSiteProperty('BannerDescription') != '') {
		// 	document.getElementById("bannerDescription").innerHTML = SCSRenderAPI.getCustomSiteProperty('BannerDescription');
		// } else if (typeof SCSRenderAPI.getCustomSiteProperty('BlogNavTitle') !== "undefined" && SCSRenderAPI.getCustomSiteProperty('BlogNavTitle') != ''){
		// 	document.getElementById("bannerDescription").innerHTML = SCSRenderAPI.getCustomSiteProperty('BlogNavTitle');
		// }

		let rssUrl;
		if (pathArray[1] === "site") {
			rssUrl = `${window.location.origin}/site/${pathArray[2]}/rss.xml`
		} else if (pathArray[1] === "post" || pathArray[1] === "category" || pathArray[1] === "authors" || pathArray[1] === "search.html" || pathArray[1] === "tag" || window.location.pathname == "/" || !window.location.pathname) {
			rssUrl = `${window.location.origin}/rss`
		} else {
			rssUrl = `${window.location.origin}/${pathArray[1]}/rss`
		}

		$('.rss-link').attr('href', rssUrl);
		$('.icn-rss').attr('href', rssUrl);
		let homepageUrl;
		let blogdirectoryUrl;
		let authordirectoryUrl;

		if (window.location.origin.includes("orasitestest") || window.location.origin.includes("blogs-stage.oracle.com")) {
			homepageUrl = "https://blogs-stage.oracle.com/";
			blogdirectoryUrl = "https://blogs-stage.oracle.com/blogdirectory";
			authordirectoryUrl = "https://blogs-stage.oracle.com/authordirectory";
		} else {
			homepageUrl = "https://blogs.oracle.com/";
			blogdirectoryUrl = "https://blogs.oracle.com/blogdirectory";
			authordirectoryUrl = "https://blogs.oracle.com/authordirectory";
		}
		$('.homepage').attr('href', homepageUrl);
		$('.blogdirectory').attr('href', blogdirectoryUrl);
		$('.authordirectory').attr('href', authordirectoryUrl);

		var LangSelected,
			Globe;
		if (typeof SCSRenderAPI.getCustomSiteProperty('LanguageSelector1') !== "undefined") {

			// document.getElementById("globe").src= "_scs_theme_root_/assets/img/globe.png";	
			LangSelected = document.getElementsByClassName("u18-langselect");
			LangSelected[0].style.display = "block";
			// Globe = document.getElementsByClassName("globe");
			// Globe[0].style.display = "block";

		}
		else {

			LangSelected = document.getElementsByClassName("u18-langselect");
			LangSelected[0].style.display = "none";
			// Globe = document.getElementsByClassName("globe");
			// Globe[0].style.display = "none";

		}
		//Adding MutlipleLanguage blogs to LangaugeGlobe on the Menu
		for (var i = 1; i < 10; i++) {
			if (typeof SCSRenderAPI.getCustomSiteProperty('LanguageSelector' + [i]) !== "undefined") {
				var LanguageSelector = [];
				LanguageSelector[i] = SCSRenderAPI.getCustomSiteProperty('LanguageSelector' + [i]);
				var strArray = LanguageSelector[i].split(",");
				var a = document.createElement("a");
				var ulist = document.getElementById("languagelist");
				var newItem = document.createElement("li");

				a.append("class", "u18v1w5v1");
				a.textContent = strArray[0];
				a.setAttribute('href', strArray[1]);
				newItem.appendChild(a);
				ulist.appendChild(newItem);
			}

		}
		/*if (typeof SCSRenderAPI.getCustomSiteProperty('SubscribeHeading') !== "undefined") {
			if (SCSRenderAPI.getCustomSiteProperty('SubscribeHeading') != '') {			
				 SubscribeHeading = SCSRenderAPI.getCustomSiteProperty('SubscribeHeading');
				 document.querySelectorAll('.subscribeheading').forEach(item => item.innerHTML = SubscribeHeading);
			}else{
				console.log("Subscribe heading is blank.");
				document.querySelectorAll('.subscribeheading').forEach(item => item.innerHTML = "Subscribe to Oracle blogs for daily alerts and stay connected");
			}
		}*/
		if (typeof SCSRenderAPI.getCustomSiteProperty('SubscribeHeading') !== "undefined" && SCSRenderAPI.getCustomSiteProperty('SubscribeHeading') != '') {
			SubscribeHeading = SCSRenderAPI.getCustomSiteProperty('SubscribeHeading');
			document.querySelectorAll('.subscribeheading').forEach(item => item.innerHTML = SubscribeHeading);
		}
		else {
			//console.log("Subscribe heading is blank.");
			document.querySelectorAll('.subscribeheading').forEach(item => item.innerHTML = "Subscribe to receive the latest blog updates");
		}
		if (typeof SCSRenderAPI.getCustomSiteProperty('EmailSubscriptionURL') !== "undefined" && document.getElementById("subscribebuttonfooter") && SCSRenderAPI.getCustomSiteProperty('EmailSubscriptionURL') != '') {
			SubscribeLink = SCSRenderAPI.getCustomSiteProperty('EmailSubscriptionURL');
			document.getElementById("subscribebuttonfooter").href = SubscribeLink;
		}
		else if (document.getElementById("subscribebuttonfooter")) {
			//console.log("Subscribe url is blank.");
			document.getElementById("subscribebuttonfooter").href = "https://go.oracle.com/LP=28277?elqCampaignId=124071&nsl=ocl&src1=:ow:o:bl:sb:::RC_WWMK210603P00162:Blogs_HP_FY22&intcmp=WWMK171102P00015:ow:o:bl:sb:::RC_WWMK210603P00162:Blogs_HP_FY22";
		}

		if (typeof SCSRenderAPI.getCustomSiteProperty('EmailSubscriptionURL') !== "undefined" && document.getElementById("subscribebuttontop") && SCSRenderAPI.getCustomSiteProperty('EmailSubscriptionURL') != '') {
			SubscribeLink = SCSRenderAPI.getCustomSiteProperty('EmailSubscriptionURL');
			document.getElementById("subscribebuttontop").href = SubscribeLink;
		}
		else if (document.getElementById("subscribebuttontop")) {
			//console.log("Subscribe url is blank.");
			document.getElementById("subscribebuttontop").href = "https://go.oracle.com/LP=28277?elqCampaignId=124071&nsl=ocl&src1=:ow:o:bl:sb:::RC_WWMK210603P00162:Blogs_HP_FY22&intcmp=WWMK171102P00015:ow:o:bl:sb:::RC_WWMK210603P00162:Blogs_HP_FY22";
		}
		if (typeof SCSRenderAPI.getCustomSiteProperty('EmailSubscriptionText') !== "undefined" && document.getElementById("subscribebuttontop") && SCSRenderAPI.getCustomSiteProperty('EmailSubscriptionText') != '') {
			SubscribeButtonTop = SCSRenderAPI.getCustomSiteProperty('EmailSubscriptionText');
			document.getElementById("subscribebuttontop").innerHTML = SubscribeButtonTop;

		}
		else if (document.getElementById("subscribebuttontop")) {
			document.getElementById("subscribebuttontop").innerHTML = "Subscribe to email updates";
		}


		if (typeof SCSRenderAPI.getCustomSiteProperty('EmailSubscriptionText') !== "undefined" && document.getElementById("subscribebuttonfooter") && SCSRenderAPI.getCustomSiteProperty('EmailSubscriptionText') != '') {
			SubscribeButtonFooter = SCSRenderAPI.getCustomSiteProperty('EmailSubscriptionText');
			document.getElementById("subscribebuttonfooter").innerHTML = SubscribeButtonFooter;
		}
		else if (document.getElementById("subscribebuttonfooter")) {
			document.getElementById("subscribebuttonfooter").innerHTML = "Subscribe to email updates";
		}
		if (typeof SCSRenderAPI.getCustomSiteProperty('SubscribeSearchBlogs') !== "undefined" && document.getElementById("subscribesearchblogs") && SCSRenderAPI.getCustomSiteProperty('SubscribeSearchBlogs') != '') {

			SubscribeSearchBlogs = SCSRenderAPI.getCustomSiteProperty('SubscribeSearchBlogs');
			document.getElementById("subscribesearchblogs").innerHTML = SubscribeSearchBlogs;
		} else if (document.getElementById("subscribesearchblogs")) {
			document.getElementById("subscribesearchblogs").innerHTML = 'Search Oracle Blogs';

		}
		// Get the <div> element
		const divElement = document.querySelector('.rh03subtitle p');
		const socialShare = document.querySelector('.social-share-wrapper'); 	
		// Check if the <div> contains text
		if (divElement.textContent.trim().length > 0) {
			socialShare.style.marginBottom = '7.5rem';
		
		}else{
			
		//	socialShare.style.marginBottom = '5.5rem';
			socialShare.classList.add("withoutcontent");
		}
		
	})

	// ----------------------------------------------
	// Create a knockout based component implemention
	// ----------------------------------------------
	var SampleComponentImpl = function (args) {
		// Initialze the custom component
		this.init(args);
	};
	// initialize all the values within the component from the given argument values
	SampleComponentImpl.prototype.init = function (args) {
		this.createViewModel(args);
		this.createTemplate(args);
		this.setupCallbacks();
	};
	// create the viewModel from the initial values
	SampleComponentImpl.prototype.createViewModel = function (args) {
		// create the viewModel
		this.viewModel = new SampleComponentViewModel(args);
	};
	// create the template based on the initial values
	SampleComponentImpl.prototype.createTemplate = function (args) {
		// create a unique ID for the div to add, this will be passed to the callback
		this.contentId = args.id + '_content_' + args.viewMode;
		// create a hidden custom component template that can be added to the DOM
		this.template = '<div id="' + this.contentId + '">' +
			template +
			'</div>';
	};
	//
	// SDK Callbacks
	// setup the callbacks expected by the SDK API
	//
	SampleComponentImpl.prototype.setupCallbacks = function () {
		//
		// callback - render: add the component into the page
		//
		this.render = $.proxy(function (container) {
			var $container = $(container);
			// add the custom component template to the DOM
			$container.append(this.template);
			// apply the bindings
			ko.applyBindings(this.viewModel, $('#' + this.contentId)[0]);
		}, this);
		//
		// callback - update: handle property change event
		//
		this.update = $.proxy(function (args) {
			var self = this;
			// deal with each property changed
			$.each(args.properties, function (index, property) {
				if (property) {
					if (property.name === 'customSettingsData') {
						self.viewModel.updateComponentData(property.value);
					} else if (property.name === 'componentLayout') {
						self.viewModel.updateLayout(property.value);
					}
				}
			});
		}, this);
		//
		// callback - dispose: cleanup after component when it is removed from the page
		//
		this.dispose = $.proxy(function () {
			// nothing required for this sample since knockout disposal will automatically clean up the node
		}, this);
	};
	// ----------------------------------------------
	// Create the factory object for your component
	// ----------------------------------------------
	var sampleComponentFactory = {
		createComponent: function (args, callback) {
			// return a new instance of the component
			return callback(new SampleComponentImpl(args));
		}
	};
	return sampleComponentFactory;
});
