angular.module("ctrlArticles",["ui.router","services"])
	.run(['$rootScope', '$state', '$stateParams', function($rootScope, $state, $stateParams) {
	}])
	.config(['$stateProvider', '$urlRouterProvider', function($stateProvider, $urlRouterProvider) {
	  $stateProvider
	  	.state("articles",{
		  	url:"/articles",
		  	templateUrl: "app/templates/articles.html",
		  	resolve: {
	            _categories: ['srvcArticles', function(srvcArticles){
					return srvcArticles.getCategoriesAll();
				}]
        	},
		  	controller: ['$rootScope', '$scope', '$state','srvcArticles', '_categories', '$stateParams', function($rootScope, $scope, $state, srvcArticles, _categories, $stateParams) {
			  	$scope.findCategoryByName = function(catName) {
				  	for (var i=$scope.categories.length;i--;) {
				  		if ($scope.categories[i].name == catName) return $scope.categories[i].id;
				  	}
				  	return null;
			  	};
			  	
			  	$scope.findCategoryById = function(catId) {
				  	for (var i=$scope.categories.length;i--;) {
				  		if ($scope.categories[i].id == catId) return $scope.categories[i].name;
				  	}	
				  	return null;
			  	};
			  	
			  	srvcArticles.getMetalPrices(function(resp){
				  	$scope.metalPrices = resp;
			  	});
		/*
	  	srvcArticles.migrateWight(function(resp){
				  	console.log(resp);
			  	});
*/
			  	$scope.categories = _categories.data;
			  	
			  	if (!$state.params.categoryName) {
				  	$scope.categoryId = $scope.categories[1].id;
				  	$state.go("articles.category",{categoryName:$scope.categories[1].name});
			  	} else {
				  	$scope.categoryId = $scope.findCategoryByName($state.params.categoryName);
			  	}
				
			  	$scope.loadCategories = function() {
			  		srvcArticles.getCategoriesAll(function(resp){
				  		$scope.categories = resp;
				  		$scope.findCategoryById($scope.categoryId);
				  		$state.go("articles.category",{categoryName:$scope.findCategoryById($scope.categoryId)});
			  		});
			  	};
			  	
			  	
			  	$scope.setCurrentCategory = function(id) {
					$scope.categoryId = id;
				};
				
				$scope.addCategory = function() {
					srvcArticles.showModalConfirm({
						title: "Добавяне на категория ",
						keys:{name:""},
						inputFieldShow: true,
						onConfirm: function(keys) {
							srvcArticles.addCategory(keys,function(resp){
								if (resp) $scope.loadCategories();
							});
						}
					});
				};
				
				$scope.editCategory = function(id, name) {
					srvcArticles.showModalConfirm({
						title: "Редактиране на категория " + name,
						keys:{id:id,name:name},
						inputFieldShow: true,
						onConfirm: function(keys) {
							srvcArticles.editCategory(keys,function(resp){
								if (resp) $scope.loadCategories();
							});
						}
					});
				};
				
				$scope.deleteCategory = function(id, name) {
					srvcArticles.showModalConfirm({
						title: "Изтриване на категория " + name,
						keys:{id:id,name:name},
						text: "Потвърждавате ли изтриването на категория " + name + " ?",
						onConfirm: function(keys) {
							srvcArticles.deleteCategory(keys,function(resp){
								if (resp) $scope.loadCategories();
							});
						}
					});
				};		  	
		  	}]
		})
		.state("articles.category",{
			url: "/{categoryName}",
			views: {
				'categoryArticlesView': {
					templateUrl: 'app/templates/categoryArticles.html',
					controller: ['$rootScope', '$scope', '$state', '$stateParams', 'srvcArticles', function ($rootScope, $scope, $state, $stateParams, srvcArticles) {
						$scope.aArticles = [];
						
						$scope.loadArticles = function() {						
							srvcArticles.getArticleInCategory($scope.findCategoryByName($stateParams.categoryName),function(resp){
								$scope.aArticles = resp;	
							});
						};
						
						$scope.loadArticles();
						
						$scope.deleteArticle = function(id, name) {
							srvcArticles.showModalConfirm({
								title: "Изтриване на артикул ",
								keys:{id:id,name:name},
								text: "Потвърждавате ли изтриването на артикул " + name + " ?",
								onConfirm: function(keys) {
									srvcArticles.deleteArticle(keys,function(resp){
										if (resp) $scope.loadArticles();
									});
								}
							});
						};
						
						$scope.editArticle = function(article) {
							srvcArticles.showModalEditArticle({
								title: "Редактиране на артикул ",
								keys:article,
								onConfirm: function(keys) {
									srvcArticles.deleteArticle(keys,function(resp){
										if (resp) $scope.loadArticles();
									});
								}
							});
						};
                	}]
            	}
            }	
		});
	}]);