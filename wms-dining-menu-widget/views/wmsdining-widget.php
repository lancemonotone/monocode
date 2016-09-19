<!-- Main App -->
<section id="dining-app" class="cf" data-ng-app="wmsDining">

	<h1 class="collection-title"><?php _e( 'Eats 4 Ephs Quick Menus' ) ?></h1>
	<h5 class="collection-date"><?php echo date( 'l n/j/Y' ) ?></h5>

	<div data-ng-controller="MenuCtrl as menu" data-ng-cloak="">

		<div class="error" data-ng-show="menu.errorMsg"><p>{{menu.errorMsg}}</p></div>

		<div class="meal-selectors">

			<div data-ng-show="menu.serviceUnits">
				<a href="javascript:void(0)"
					class="button blue-button rounded-button"
					ng-class="{service_units_active: menu.isServiceUnitSelected(unit.unitid)}"
					data-ng-repeat="unit in menu.serviceUnits"
					data-ng-click="menu.getMealsByServiceUnit(unit.unitid)">{{unit.service_unit}}</a>
			</div>

			<div data-ng-show="menu.showMeals()">
				<a href="javascript:void(0)"
					class="button blue-button rounded-button"
					ng-class="{meals_active: menu.isMealSelected(meal)}"
					data-ng-repeat="meal in menu.mealList"
					data-ng-click="menu.getMenuByMeal(meal)"
					data-ng-hide="menu.fetchingData">{{meal.ucwords()}}</a>
			</div>

		</div>

		<div class="loader" data-ng-show="menu.fetchingData"></div>

		<div class="meal-list" data-ng-show="menu.showMealList()">

			<ul>
				<li data-ng-repeat="(course, items) in menu.filteredMeals">
					<strong>{{course}}</strong>
					<ul>
						<li data-ng-repeat="item in items">
							{{item.formal_name}}
						</li>
					</ul>
				</li>
			</ul>

			<div class="info-footer cf">
				<p>GF = Gluten Free, V = Vegan</p>
				<a class="info-link button rounded-button"
					data-ng-show="menu.showFullMenuLink()"
					target="_blank"
					href="http://nutrition.williams.edu/NetNutrition/Home.aspx?unit={{menu.selectedServiceUnit.net_nutrition}}&date=today&meal={{menu.selectedMeal}}"
					title="Full menu and nutrition from NetNutrition"><?php _e( 'Full menu &amp nutrition info' ) ?></a>

				<a class="info-link button rounded-button"
					data-ng-show="menu.showBookmarkLink()"
					href="{{menu.thisURL.scheme}}://{{menu.thisURL.authority}}{{menu.thisURL.path}}?unitid={{menu.selectedServiceUnit.unitid}}&meal={{menu.selectedMeal}}"
					title="Menu for {{menu.selectedServiceUnit.service_unit}} : {{menu.selectedMeal}}"><?php _e( 'Bookmark this menu' ) ?></a>


			</div>

		</div>
	</div>
</section>