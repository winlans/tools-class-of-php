<?php
namespace Charge\Util;

use \Exception;
use \ReflectionProperty;
use \ReflectionFunction;
use \ReflectionException;
use \ReflectionClass;

class ClassUtil
{
    /**
     * 获取一个类的属性， 支持filter函数
     * @param $classname
     * @param null $filter
     * @return array
     * @throws ReflectionException
     */
    public static function propertiesOfClass($classname, $filter = null) {
        $pros = [];
        try{
            if (! class_exists($classname)) {
                throw new Exception(sprintf("class %s not load or not exists", $classname));
            }

            $refO = new ReflectionClass($classname);
            $properties = $refO->getProperties();
            foreach ($properties as $property) {
                if (! is_null($filter)) {
                    if (! is_callable($filter)) {
                        throw new Exception('$filter must be a clourse');
                    }

                    $refF = new ReflectionFunction($filter);
                    if ($refF->getNumberOfParameters() != 1) {
                        throw new Exception(sprintf("param %s instanceof %s and is necessary", '$property', ReflectionProperty::class));
                    }
                    if ($ok = $filter($property)) {
                        $pros[] = $ok;
                    }
                } else {
                    $pros[] = $property->getName();
                }
            }
        }catch (ReflectionException $exception) {
            throw $exception;
        }

        return $pros;
    }

    /**
     * 获取一个类的自有属性(排除继承过来的属性)
     * @param $classname
     * @return array
     * @throws ReflectionException
     */
    public static function propertiesOfOwnClass($classname) {
        $filter = function (ReflectionProperty $property) use($classname){
            $class = ($property->getDeclaringClass());
            if ($class->getName() == $classname) {
                return $property->getName();
            }
            return null;
        };

        return self::propertiesOfClass($classname, $filter);
    }
}
