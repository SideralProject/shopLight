angular.module('services',['angularModalService'])
	.factory('srvcArticles', ['$http', 'ModalService', function ($http,ModalService) {
		var _request = function(_params,onSuccess) {
				return $http({url: "include/main.php", method: "POST", data:$.param(_params), headers: {'Content-Type':'application/x-www-form-urlencoded;charset=utf-8'}})
				.success(function(resp){ 
					if (resp.error) {
						console.log("Server error:", resp.error.code, resp.error.msg);
						return;
					}
					if (typeof onSuccess == "function") onSuccess(resp.data);
				})
				.error(function(data, status, headers, config){console.log("System error: " + data, status);})
				.then(function(resp){ /*console.log("me",resp.config);*/ return resp.data; });
			
		};
		
		var factory = {};
		
		factory.migrateWight = function(onSuccess) {
			return _request({act:"migrateWight"},onSuccess);
		};
		
		factory.getAdverts = function(onSuccess) {
			return _request({act:"getAdverts"},onSuccess);
		};
		
		factory.setAdverts = function(adverts,onSuccess) {
			var _params =  {
				act: "setAdverts",
				txt1: adverts[0].txt,
				txt2: adverts[1].txt,
				txt3: adverts[2].txt,
				active1: adverts[0].active,
				active2: adverts[1].active,
				active3: adverts[2].active,
			};
			return _request(_params,onSuccess);
		};
		
		factory.getMetalPrices = function(onSuccess) {
			return _request({act:"getMetalPrices"},onSuccess);
		};
		
		factory.setMetalPrices = function(_params,onSuccess) {
			_params.act = "setMetalPrices";
			return _request(_params,onSuccess);	
		};
		
		factory.getCategoriesAll = function(onSuccess) {
			return _request({act:"getCategories"},onSuccess)			
		};
				 
		factory.getCategoryById = function(id) {
			
		};
		
		factory.addCategory = function(_params,onSuccess) {
			_params.act = "addCategory";
			_params.user = "1";
			return _request(_params,onSuccess);
		};
		
		factory.deleteCategory = function(_params,onSuccess) {
			_params.act = "deleteCategory";
			_params.user = "1";
			return _request(_params,onSuccess);
		};
		
		factory.editCategory = function(_params, onSuccess) {
			_params.act = "editCategory";
			_params.user = "1";
			return _request(_params,onSuccess);
		};
		
		factory.getArticleInCategory = function (id, onSuccess) {
			return _request({act: "getArticleInCategory", id: id},onSuccess);		
		};
		
		factory.deleteArticle = function(_params,onSuccess) {
			_params.act = "deleteArticle";
			_params.user = "1";
			return _request(_params,onSuccess);
		};
			
		factory.showModalConfirm = function(params) {
			ModalService.showModal({
				templateUrl: "app/templates/modalConfirm.html",
				controller: ['$scope','close',function($scope,close){
					$scope.settings = params;
					$scope.isUndefined = function(value) { 
						return typeof(value) === "undefined"; 
					};
					
					$scope.confirm = function(keys) {
						if (typeof $scope.settings.onConfirm == "function") $scope.settings.onConfirm(keys);
					};
					$scope.close = function(result) {
						close(result, 500);
					};
				}]
		    }).then(function(modal) {
				modal.element.modal();
				modal.close.then(function(result) {

				}); 
		    });
		};
		
		factory.showModalEditArticle = function(params) {
			ModalService.showModal({
				templateUrl: "app/templates/modalEditArticle.html",
				controller: ['$scope','close',function($scope,close){
					$scope.settings = params;
					$scope.article = params.keys;
					$scope.isUndefined = function(value) { 
						return typeof(value) === "undefined"; 
					};
					
					$scope.confirm = function(keys) {
						if (typeof $scope.settings.onConfirm == "function") $scope.settings.onConfirm(keys);
					};
					$scope.close = function(result) {
						close(result, 500);
					};
				}]
		    }).then(function(modal) {
			    modal.windowClass =  'app-modal-window';
				modal.element.modal();
				modal.close.then(function(result) {

				}); 
		    });
		};
		
		return factory;
	}]);
		