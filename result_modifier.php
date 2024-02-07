<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
 
/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogElementComponent $component
 */

$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();

$dbComment = CIBlockElement::GetList(
  array("id"=>"desc"), 
  array("SECTION_CODE"=>$arResult["ID"]), false, false, array("IBLOCK_ID", "ID", "NAME", "DATE_CREATED", "PROPERTY_DIGNITY","PROPERTY_CREATED","PROPERTY_FLAWS","PROPERTY_COMMENT", "PROPERTY_LIKES", "CREATED_BY", "PROPERTY_LIKED_BY"));
while($arComment = $dbComment->GetNext()){
  $arResult["COMMENTS"][] = $arComment;
}

foreach($arResult["PROPERTIES"]["RECOMMENDED"]["VALUE"] as $key => $value){
  $dbRecommended = CIBlockElement::GetList(
    array("sort"=>"asc"), 
    array("ID"=>$value), false, false, array("IBLOCK_ID", "ID", "NAME", "QUANTITY", "PRICE_1", "PROPERTY_MORE_PHOTO",'DETAIL_PICTURE', "DETAIL_PAGE_URL"));
  if($arRecommended = $dbRecommended->GetNext()){
    $arResult["PROPERTIES"]["RECOMMENDED"]["VALUE"][$key]=$arRecommended;
  }
}



// foreach($arResult["PROPERTIES"]["RECOMMENDED"]["VALUE"] as $key => $value){
//   $dbItems = CIBlockElement::GetList(array("SORT"=>"asc"), )
// }