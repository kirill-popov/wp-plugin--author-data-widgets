(function( $ ) {
	'use strict';


	function author_field_autocomplete_handler() {
		let field_class = '.author-data-widget-author';
		$('body').on('click', field_class, function() {


		let autocomplete_field = $(field_class);
		let boldStr = function(needle, haystack) {
			let regex = new RegExp(needle, 'i');
			return haystack.replace(regex, function(matched) {
				return "<span style='font-weight:bold;'>" + matched + "</span>";
			});
		}

		if (autocomplete_field.length) {
			autocomplete_field.each(function() {
				let name_field = $(this);

				let id_field = name_field.next();
				name_field.autocomplete({
					source: function(request, response) {
						let name_field_value = name_field.val();
						$.ajax({
							type: 'POST',
							url: ajaxurl,
							data: {
								action: "widget_author_autocomplete",
								s: name_field_value
							},
							success: function(data) {
								if (data.length) {
									response($.map(data, function(item) {
										return {
											label: item.name,
											value: item.id
										};
									}));
								} else {
									response([{
										label: "Nothing found",
										value: ""
									}]);
								}
							}
						});
					},
					search: function(event, ui) {
						id_field.val('');
					},
					select: function(event, ui) {
						event.preventDefault();
						let item = ui.item;
						let user_id = parseInt(item.value);
						if ("undefined" !== typeof id_field
						&& Number.isInteger(user_id)) {
							name_field.val(item.label);
							id_field.val(item.value);
						}
					},
					minLength: 1,
				})
				.autocomplete("instance")._renderItem = function(ul, item) {
					let value = '';
					if ('Nothing found' == item.label) {
						value = item.label;
					} else {
						value = boldStr(name_field.val(), item.label);
					}
					return $("<li>")
					.append("<div>" + value + "</div>")
					.appendTo(ul);
				}
			});
		}
		});
	}

	$(function() {
		author_field_autocomplete_handler();
	});
})(jQuery);