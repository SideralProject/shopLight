angular.module("ctrlSettings",["ui.router","services"])
	.run(['$rootScope', '$state', '$stateParams', function($rootScope, $state, $stateParams) {

	}])
	.config(['$stateProvider', '$urlRouterProvider', function($stateProvider, $urlRouterProvider) {
	  $stateProvider
	  	.state("settings",{
		  	url:"/settings",
		  	templateUrl: "app/templates/settings.html",
		  	controller: ['$rootScope', '$scope', '$state','srvcArticles', '$stateParams', function($rootScope, $scope, $state, srvcArticles, $stateParams) {
				srvcArticles.getMetalPrices(function(resp){
					$scope.metalPrices = resp;
				});
				
				srvcArticles.getAdverts(function(resp){
					$scope.adverts = resp;
				});
				
				$scope.previewAdvert = function() {
					try {
					clearInterval($scope.tmrAdvert);
					$scope.tmrAdvert = null;
					$("#divReklama").html("");
					var _aR = [];
					for (var i=0;i<3;i++) {
						if (parseInt($scope.adverts[i].active) == 1) _aR.push($scope.adverts[i]);
					}
					var cnt = 0;
					var len = _aR.length;	
					if (len == 0) {
						return;
					}
					$("#divReklama").html(_aR[cnt].txt).show("slide",{ direction: "right" });
					 $scope.tmrAdvert = setInterval(function(){
						if ($("#divReklama").css("visibility") == "hidden") $("#divReklama").html(_aR[cnt].txt).show("slide",{ direction: "right" });
						else $("#divReklama").hide("slide",function(){
							$("#divReklama").html(_aR[cnt].txt).show("slide", { direction: "right" });
						});
						if (cnt == len - 1) cnt = 0;
						else cnt++;		
					},5000);
					} catch(e) {
						console.log(e);
					}
				};
				
				$scope.setAdvert = function() {
					srvcArticles.setAdverts($scope.adverts,function(resp){
// 						$scope.adverts = resp;
					});
				};
				
				$scope.setMetalPrices = function(metal) {
					var _metal = {};
					_metal[metal] = $scope.metalPrices[metal];
					srvcArticles.setMetalPrices(_metal,function(resp){
						
					});
				};
		  	}]
		});
	}]);