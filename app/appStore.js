var glob;
angular.module("appStore",["ui.router","ngAnimate","ctrlArticles","ctrlSettings","angularModalService","ui.equalHeights"])
	.run(['$rootScope', '$state', '$stateParams', function($rootScope, $state, $stateParams) {
	  $rootScope.$state = $state;
	  $rootScope.$stateParams = $stateParams;
	  $rootScope.currentCategory = false;
	}])
	.config(['$stateProvider', '$urlRouterProvider', function($stateProvider, $urlRouterProvider) {
		$urlRouterProvider
		  	.when("/","/articles")
		  	.when("/articles/*","/articles/:categoryName")
		  	.otherwise("/articles");
		  	
	}]);
	
	
	function EqualHeightsDirective($timeout) {
	  function link($scope, $element, attrs) {
	    $timeout(function() {
	      var $children        = $element.children(),
	          currentMaxHeight = 0,
	          numImagesLoaded  = 0,
	          $images          = $element.find('img'),
	          imageCount       = $images.length;
	 
	      if (imageCount > 0) {
	        angular.forEach($images, function(image) {
	          if (image.complete) {
	            numImagesLoaded++;
	          }
	        });
	      }
	     
	      if (numImagesLoaded === imageCount) {
	        angular.forEach($children, function(child) {
	          var childHeight = $(child).outerHeight();
	 
	          if (childHeight > currentMaxHeight) {
	            currentMaxHeight = childHeight;
	          }
	        });
	        // set heights
	        $children.css({height: currentMaxHeight});
	        $children.children().css({height: currentMaxHeight - 20});
	      }
    	});
  	}
 
	return {
		restrict: 'A',
		scope: {},
		link: link
	};
};
 
angular
  .module('ui.equalHeights', [])
  .directive('equalHeights', EqualHeightsDirective)