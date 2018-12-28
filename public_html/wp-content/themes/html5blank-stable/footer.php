			<!-- footer -->
			<footer class="footer" role="contentinfo">

				<!-- copyright -->
				<p class="copyright">
					&copy; <?php echo date('Y'); ?> Earth Sign Designs
				</p>
				<!-- /copyright -->

			</footer>
			<!-- /footer -->

		</div>
		<!-- /wrapper -->

		<?php wp_footer(); ?>

		<!-- analytics -->
		<script>
		function toggleNav() {
    var x = document.getElementById("toggleNav");
    if (x.className === "toggleNav") {
        x.className += " active";
    } else {
        x.className = "toggleNav";
    }
}
		</script>

		<script>
			$(function() {
				$(window).on("scroll", function() {
						if($(window).scrollTop() > 50) {
								$(".desktopNav").addClass("active");
						} else {
								//remove the background property so it comes transparent again (defined in your css)
							 $(".desktopNav").removeClass("active");
						}
				});
			});
		</script>

	</body>
</html>
