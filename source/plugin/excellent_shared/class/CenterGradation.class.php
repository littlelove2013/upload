<?php

/**
 * Created by PhpStorm.
 * User: 龚成
 * Date: 2016/2/18
 * Time: 20:46
 */
require_once ("ThreadWithType.class.php");
require_once ("DiscuzDataTree.class.php");
require_once ("DB.class.php");
//中间层类
class CenterGradation
{
    private static $maintype_id=0;
    //获取主分类的第1层type_id
    public static function getMainTypeIdFromDB(){
        $db=GCDB::getInstance();
        $sql="SELECT `type_id` FROM `{$db->tablepre}forum_gc_type_thread` WHERE type_level=1";
        $sqldata=$db->query($sql);
        if(!$sqldata){
            return array(false,"查询数据库失败");
        }
        $maintype_id=0;
        $row=$sqldata->fetch_assoc();
        $maintype_id=$row['type_id'];
        while($row=$sqldata->fetch_assoc()){
            if($maintype_id>$row['type_id']){
                $maintype_id=$row['type_id'];
            }
        }
        return array(true,$maintype_id);
    }
    public static function getMainTypeId(){
        if (self::$maintype_id==0){
            //从数据库获取
            $result=self::getMainTypeIdFromDB();
            if($result[0]){
                self::$maintype_id=$result[1];
            }else{
                return array(false,"从数据库查找主分类失败");
            }
        }
        return array(true,self::$maintype_id);
    }
    //判断指定参数id是否是主分类
    public static function isMainType($type_id){
        if(!is_numeric($type_id)){
            return array('0'=>false,'message'=>"参数应为整型");
        }
        $tree=DataTree::getInstance();
        $result=$tree->getANodeByOtherNode($type_id,1);
        if($result[0]){
            //查找成功
            $node=$result[1];
            // $root=$tree->getOneTreeNode();
            $maintype=self::getMainTypeId();
            if($maintype[0]){
                $maintype_id=$maintype[1];
            }else{
                return array('0'=>false,'message'=>"未查找到主分类id");
            }
            if($node->type_id == $maintype_id){
                return array('0'=>true,'ismaintype'=>true);
            }else{
                return array('0'=>true,'ismaintype'=>false);
            }
        }else{
            return array('0'=>false,'message'=>"查找祖先节点失败");
        }
    }
    /*
     *
     * 删除分类节点下的所有帖子关系（即获取该type_id指定的所有帖子tid并按照tid删除数据库关系表）
     * 1、为次分类，则不做处理，按照数据库级联删除即可
     * 2、若为主分类，则获取与主分类关联的所有帖子tid，并循环按照tid对关系表进行删除
     *
     */

    public static function deleteAllThreadWithMainType($type_id){
        $reslut=self::isMainType($type_id);
        if($reslut[0]){
            //正确获取分类信息
            if(!$reslut['ismaintype']){
                //是次分类，则直接返回正确
                return array('0'=>true,'type'=>'subordinate');
            }else{
                //是主分类，则需要删除数据库
                $db=GCDB::getInstance();
                $sql="SELECT `tid` FROM `{$db->tablepre}forum_gc_excellent_thread` WHERE `type_id`={$type_id};";
                $sqldata=$db->query($sql);
                if(!$sqldata){
                    return array('0'=>false,'message'=>"查询数据库失败");
                }
                while($row=$sqldata->fetch_assoc()){
                    $tid=$row['tid'];
                    $sql="DELETE FROM `{$db->tablepre}forum_gc_excellent_thread` WHERE `tid`={$tid};";
                    if($db->query($sql)==false){
                        return array('0'=>false,'message'=>"删除数据库出错");
                    }
                }
                return array('0'=>true,'type'=>'maintype');
            }
        }else{
            return array('0'=>false,'message'=>"未查找到该分类信息");
        }
    }
}

?>