$(function() {

        if (typeof avatarURL !== 'undefined') {

			$('.rotate-preview').click(function(){
				
				console.log("rotate");
				
				_rotation = $(this).parent().find('.hidden-image-data-rotate').val();
				_rotation = parseInt(_rotation) + 1;			

				if(_rotation >= 4) {
					_rotation = 0;
				}

				

				$(this).parent().parent().find('.cropit-preview').css('transform', 'rotate(' + _rotation * 90 + 'deg)');
				$(this).parent().parent().find('.hidden-image-data-rotate').val(_rotation);

			});

			

			$('.image-editor').cropit({
				width: 200,
				height: 200,
				imageState: {
					src: avatarURL,
				},
				onFileChange: function(object) {
					console.log(object);
				},

				onFileReaderError: function(object) {
					console.log(object);
				},

				onImageError: function(object) {
					console.log(object);
					alert('Tu imagen no es compatible. Debe tener al menos 200px x 200px ser JPG, PNG o GIF. Intenta nuevamente.');
				},
				onImageLoading: function(object) {
				},
				onImageLoaded: function(object) {
					console.log("loaded");
					$(".showOnload").show();
				}

			});



			$('form').submit(function() {
			  $imageCropper = $('.image-editor');
			  var imageData = $imageCropper.cropit('export', {
				  type: 'image/png',
				  quality: .75,
				  originalSize: false
			  });

			  $('.hidden-image-data').val(imageData);

			});
		
		}

});