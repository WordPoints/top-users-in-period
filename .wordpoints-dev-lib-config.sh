#!/usr/bin/env bash

wordpoints-dev-lib-config() {

	CODESNIFF_PHP_AUTOLOADER_DEPENDENCIES+=( \
		"${WORDPOINTS_DEVELOP_DIR}/src/components/points/classes/" \
		"${WORDPOINTS_DEVELOP_DIR}/src/components/points/includes/" \
		"${WORDPOINTS_DEVELOP_DIR}/src/admin/classes/" \
	)

	export CODESNIFF_PHP_AUTOLOADER_DEPENDENCIES
}

# EOF
