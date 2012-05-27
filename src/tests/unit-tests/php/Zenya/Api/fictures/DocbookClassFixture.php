<?php
namespace Zenya\Api\fixtures;

/**
 * Class Title
 *
 * Class description 1st line
 * Class description 2nd line
 *
 * @param int $classNamedInteger an integer
 * @param string $classNamedString a string
 * @param boolean $classNamedBoolean a boolean
 * @param array $classOptional something optional (here an array)
 * @api_public true
 * @api_version 1.0
 * @api_permission admin
 * @api_randomName classRandomValue
 */
class DocbookClassFixture {
    /**
     * Title
     *
     * Description 1st line
     * Description 2nd line
     *
     * @param int $namedInteger an integer
     * @param string $namedString a string
     * @param boolean $namedBoolean a boolean
     * @param array $optional something optional (here an array)
     * @return array result
     * @api_public true
	 * @api_version 1.0
	 * @api_permission admin
     * @api_randomName randomValue
     */
    public static function methodNameOne($namedInteger, $namedString, $namedBoolean, array $optional=null) {
        return array($namedInteger, $namedString, $namedBoolean, $optional);
    }
}