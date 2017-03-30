#!/usr/bin/env bash

wordpoints-dev-lib-config() {

	CODESNIFF_PHP_AUTOLOADER_DEPENDENCIES+=( \
		"${WORDPOINTS_DEVELOP_DIR}/src/components/points/classes/" \
		"${WORDPOINTS_DEVELOP_DIR}/src/components/points/includes/" \
	)

	export CODESNIFF_PHP_AUTOLOADER_DEPENDENCIES
}

# EOF
