<?php
/**
 * Created by PhpStorm.
 * User: abejarano
 * Date: 21/03/17
 * Time: 03:55 PM
 */

namespace fw_Klipso\kernel\engine\dataBase;


use function fw_Klipso\kernel\engine\dataBase\Sync\getInstancModel;

class Constrainst
{
    static function ForeignKey($model_reference, $field_reference,
                               $on_delete = "NO ACTION", $on_update = "NO ACTION")
    {
        /* se obtiene el prefijo del model */
        $prefix = getPrefixModel($model_reference);
        if(!empty($prefix))
            $model_reference = $prefix . '_' .$model_reference;

        $field_reference = trim(strtolower($field_reference));
        $params = unserialize(DATABASE);
        $driver = $params['ENGINE'];

        $name_constraict = "{origin}_" . substr($model_reference,6) . "_" . "{field_origin}_" . substr($field_reference,6);

        /* crea un indice para mejorar rendimientos de los join */
        if ($driver == 'mysql'){
            $fk = "ALTER TABLE {origin} ADD INDEX indx_{origin}_{field_origin} ({field_origin} ASC);";
        }elseif ($driver == 'pgsql'){
            $fk = "
                CREATE INDEX indx_{origin}_{field_origin} ON {origin}({field_origin} ASC NULLS LAST);";
        }
        $fk .= "ALTER TABLE {origin}
                ADD CONSTRAINT fk_" . strtolower($name_constraict) . " FOREIGN KEY ({field_origin})
                REFERENCES ".strtolower($model_reference)."($field_reference) MATCH SIMPLE
                ON UPDATE $on_update
                ON DELETE $on_delete;";

        return $fk;
    }

    static function on_update($cascade = false){
        if(!$cascade)
            return 'NO ACTION';
        else
            return 'CASCADE';
    }

    /**
     * Por defecto on delete es casada, para que sea restrict $casada debe ser True
     * @param bool $cascade
     * @return string
     */
    static function on_delete($cascade = false){
        if(!$cascade)
            return 'RESTRICT';
        else
            return 'CASCADE';
    }
}