<?php
namespace App\Models;

trait HasSlugTrait{
    
    public static function findSlug($slug = false){
        $field = property_exists( new self, 'slug_field') ? self::$sbid_field : 'slug';
        if ($slug){
            $slug = strtolower(preg_replace("/[^a-zA-Z\d]/", "-", $slug));
        }else{
            $slug = self::generateRandomString(32);
        }
        $exists = self::where($field, $slug)->first();
        if (!$exists && $slug != 'new'){
            return $slug;
        }
        $i = 0;
        while ($exists || $slug == 'new'){
            $exists = self::where($field, $slug."-".++$i)->first();
        }
        return $slug."-".$i;
    }
    
    public static function generateRandomString($length = 10) {
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';

	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }

	    return $randomString;
	}
}
