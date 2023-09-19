(function( $ ) {
	'use strict';

	$(function() {
		class Widget {
			widgetElement;
			widgetForm;
			authorIDField;
			authorIDFieldName = 'author_id';
			messageField;
			messageFieldName = 'message';
			nonceField;
			nonceFieldName = '_nonce';
			messagesListElement;
			submitButton;

			constructor(el) {
				this.widgetElement = $(el);
				this.widgetForm = this.widgetElement.find('.author-messages-form');
				this.messagesListElement = this.widgetElement.find('.messages-list');
				this.submitButton = this.widgetElement.find('input[type=submit]');

				this.addFormSubmitHandler();
			}

			addFormSubmitHandler() {
				this.messageField = this.widgetForm.find('[name='+this.messageFieldName+']');
				this.authorIDField = this.widgetForm.find('[name='+this.authorIDFieldName+']');
				this.nonceField = this.widgetForm.find('[name='+this.nonceFieldName+']');

				this.widgetForm.on('submit', (e) => {
					e.preventDefault();
					this.sendMessage({
						[this.messageFieldName]: this.messageField.val(),
						[this.authorIDFieldName]: this.authorIDField.val(),
						[this.nonceFieldName]: this.nonceField.val()
					});
				});
			}

			sendMessage(data) {
				data.action = 'save_widget_author_messages';
				$.ajax({
					url: myajax.url,
					type: 'POST',
					data: data,
					dataType: 'json',
					beforeSend: () => {
						this.submitButton.attr('disabled', 'disabled');
					},
					success: (resp) => {
						if (resp.success) {
							this.messageField.val('');
							this.addMessage(data);
						}
					},
					complete: () => {
						this.submitButton.removeAttr('disabled');
					}
				});
			}

			addMessage(data) {
				this.messagesListElement.append('<li>'+data[this.messageFieldName]+'</li>');
			}
		};

		let SecureAuthorDataWidgets = {
			widgets: [],
			classIdentifier: '.widget_author_posts_widget',

			init: function() {
				$('body').find(this.classIdentifier).each((index, el) => {
					this.widgets.push(new Widget(el));
				});
			}
		};

		SecureAuthorDataWidgets.init();
	});
})(jQuery);