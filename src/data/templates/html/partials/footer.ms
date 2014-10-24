			{{# getVersion }}
			<h2>Vesion information</h2>
			    <p>
			       This API manpage describes {{{ getVersion }}}.
			    </p>
			{{/ getVersion }}

			<h2>Copyright</h2>
				<p>Powered by APIx-server, Copyright (C) 2010 Franck Cassedanne</p>

			<ol class='man-decor man-foot man foot'>
			  <li class='tl'>{{ config.api_version }}</li>
			  <li class='tc'>...</li>
			  <li class='tr'>{{ config.api_realm }}</li>
			</ol>
		</div>
	</body>
</html>