<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Catalog\ProductTable;

/**
* @global CMain $APPLICATION
* @var array $arParams
* @var array $arResult
* @var CatalogSectionComponent $component
* @var CBitrixComponentTemplate $this
* @var string $templateName
* @var string $componentPath
* @var string $templateFolder
*/

$this->setFrameMode(true);

$templateLibrary = array('popup', 'fx', 'ui.fonts.opensans');
$currencyList = '';

if (!empty($arResult['CURRENCIES']))
{
	$templateLibrary[] = 'currency';
	$currencyList = CUtil::PhpToJSObject($arResult['CURRENCIES'], false, true, true);
}

$haveOffers = !empty($arResult['OFFERS']);

$templateData = [
	'TEMPLATE_THEME' => $arParams['TEMPLATE_THEME'],
	'TEMPLATE_LIBRARY' => $templateLibrary,
	'CURRENCIES' => $currencyList,
	'ITEM' => [
		'ID' => $arResult['ID'],
		'IBLOCK_ID' => $arResult['IBLOCK_ID'],
	],
];
if ($haveOffers)
{
	$templateData['ITEM']['OFFERS_SELECTED'] = $arResult['OFFERS_SELECTED'];
	$templateData['ITEM']['JS_OFFERS'] = $arResult['JS_OFFERS'];
}
unset($currencyList, $templateLibrary);

$mainId = $this->GetEditAreaId($arResult['ID']);
$itemIds = array(
	'ID' => $mainId,
	'DISCOUNT_PERCENT_ID' => $mainId.'_dsc_pict',
	'STICKER_ID' => $mainId.'_sticker',
	'BIG_SLIDER_ID' => $mainId.'_big_slider',
	'BIG_IMG_CONT_ID' => $mainId.'_bigimg_cont',
	'SLIDER_CONT_ID' => $mainId.'_slider_cont',
	'OLD_PRICE_ID' => $mainId.'_old_price',
	'PRICE_ID' => $mainId.'_price',
	'DISCOUNT_PRICE_ID' => $mainId.'_price_discount',
	'PRICE_TOTAL' => $mainId.'_price_total',
	'SLIDER_CONT_OF_ID' => $mainId.'_slider_cont_',
	'QUANTITY_ID' => $mainId.'_quantity',
	'QUANTITY_DOWN_ID' => $mainId.'_quant_down',
	'QUANTITY_UP_ID' => $mainId.'_quant_up',
	'QUANTITY_MEASURE' => $mainId.'_quant_measure',
	'QUANTITY_LIMIT' => $mainId.'_quant_limit',
	'BUY_LINK' => $mainId.'_buy_link',
	'ADD_BASKET_LINK' => $mainId.'_add_basket_link',
	'BASKET_ACTIONS_ID' => $mainId.'_basket_actions',
	'NOT_AVAILABLE_MESS' => $mainId.'_not_avail',
	'COMPARE_LINK' => $mainId.'_compare_link',
	'TREE_ID' => $haveOffers && !empty($arResult['OFFERS_PROP']) ? $mainId.'_skudiv' : null,
	'DISPLAY_PROP_DIV' => $mainId.'_sku_prop',
	'DESCRIPTION_ID' => $mainId.'_description',
	'DISPLAY_MAIN_PROP_DIV' => $mainId.'_main_sku_prop',
	'OFFER_GROUP' => $mainId.'_set_group_',
	'BASKET_PROP_DIV' => $mainId.'_basket_prop',
	'SUBSCRIBE_LINK' => $mainId.'_subscribe',
	'TABS_ID' => $mainId.'_tabs',
	'TAB_CONTAINERS_ID' => $mainId.'_tab_containers',
	'SMALL_CARD_PANEL_ID' => $mainId.'_small_card_panel',
	'TABS_PANEL_ID' => $mainId.'_tabs_panel'
);
$obName = $templateData['JS_OBJ'] = 'ob'.preg_replace('/[^a-zA-Z0-9_]/', 'x', $mainId);
$name = !empty($arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'])
	? $arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']
	: $arResult['NAME'];
$title = !empty($arResult['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_TITLE'])
	? $arResult['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_TITLE']
	: $arResult['NAME'];
$alt = !empty($arResult['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_ALT'])
	? $arResult['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_ALT']
	: $arResult['NAME'];

if ($haveOffers)
{
	$actualItem = $arResult['OFFERS'][$arResult['OFFERS_SELECTED']] ?? reset($arResult['OFFERS']);
	$showSliderControls = false;

	foreach ($arResult['OFFERS'] as $offer)
	{
		if ($offer['MORE_PHOTO_COUNT'] > 1)
		{
			$showSliderControls = true;
			break;
		}
	}
}
else
{
	$actualItem = $arResult;
	$showSliderControls = $arResult['MORE_PHOTO_COUNT'] > 1;
}

$skuProps = array();
$price = $actualItem['ITEM_PRICES'][$actualItem['ITEM_PRICE_SELECTED']];
$measureRatio = $actualItem['ITEM_MEASURE_RATIOS'][$actualItem['ITEM_MEASURE_RATIO_SELECTED']]['RATIO'];
$showDiscount = $price['PERCENT'] > 0;

if ($arParams['SHOW_SKU_DESCRIPTION'] === 'Y')
{
	$skuDescription = false;
	foreach ($arResult['OFFERS'] as $offer)
	{
		if ($offer['DETAIL_TEXT'] != '' || $offer['PREVIEW_TEXT'] != '')
		{
			$skuDescription = true;
			break;
		}
	}
	$showDescription = $skuDescription || !empty($arResult['PREVIEW_TEXT']) || !empty($arResult['DETAIL_TEXT']);
}
else
{
	$showDescription = !empty($arResult['PREVIEW_TEXT']) || !empty($arResult['DETAIL_TEXT']);
}
$showBuyBtn = in_array('BUY', $arParams['ADD_TO_BASKET_ACTION']);
$buyButtonClassName = in_array('BUY', $arParams['ADD_TO_BASKET_ACTION_PRIMARY']) ? 'btn-primary' : 'btn-link';
$showAddBtn = in_array('ADD', $arParams['ADD_TO_BASKET_ACTION']);
$showButtonClassName = in_array('ADD', $arParams['ADD_TO_BASKET_ACTION_PRIMARY']) ? 'btn-primary' : 'btn-link';
$showSubscribe = $arParams['PRODUCT_SUBSCRIPTION'] === 'Y' && ($arResult['PRODUCT']['SUBSCRIBE'] === 'Y' || $haveOffers);

$arParams['MESS_BTN_BUY'] = $arParams['MESS_BTN_BUY'] ?: Loc::getMessage('CT_BCE_CATALOG_BUY');
$arParams['MESS_BTN_ADD_TO_BASKET'] = $arParams['MESS_BTN_ADD_TO_BASKET'] ?: Loc::getMessage('CT_BCE_CATALOG_ADD');

if ($arResult['MODULES']['catalog'] && $arResult['PRODUCT']['TYPE'] === ProductTable::TYPE_SERVICE)
{
	$arParams['~MESS_NOT_AVAILABLE'] = $arParams['~MESS_NOT_AVAILABLE_SERVICE']
		?: Loc::getMessage('CT_BCE_CATALOG_NOT_AVAILABLE_SERVICE')
	;
	$arParams['MESS_NOT_AVAILABLE'] = $arParams['MESS_NOT_AVAILABLE_SERVICE']
		?: Loc::getMessage('CT_BCE_CATALOG_NOT_AVAILABLE_SERVICE')
	;
}
else
{
	$arParams['~MESS_NOT_AVAILABLE'] = $arParams['~MESS_NOT_AVAILABLE']
		?: Loc::getMessage('CT_BCE_CATALOG_NOT_AVAILABLE')
	;
	$arParams['MESS_NOT_AVAILABLE'] = $arParams['MESS_NOT_AVAILABLE']
		?: Loc::getMessage('CT_BCE_CATALOG_NOT_AVAILABLE')
	;
}

$arParams['MESS_BTN_COMPARE'] = $arParams['MESS_BTN_COMPARE'] ?: Loc::getMessage('CT_BCE_CATALOG_COMPARE');
$arParams['MESS_PRICE_RANGES_TITLE'] = $arParams['MESS_PRICE_RANGES_TITLE'] ?: Loc::getMessage('CT_BCE_CATALOG_PRICE_RANGES_TITLE');
$arParams['MESS_DESCRIPTION_TAB'] = $arParams['MESS_DESCRIPTION_TAB'] ?: Loc::getMessage('CT_BCE_CATALOG_DESCRIPTION_TAB');
$arParams['MESS_PROPERTIES_TAB'] = $arParams['MESS_PROPERTIES_TAB'] ?: Loc::getMessage('CT_BCE_CATALOG_PROPERTIES_TAB');
$arParams['MESS_COMMENTS_TAB'] = $arParams['MESS_COMMENTS_TAB'] ?: Loc::getMessage('CT_BCE_CATALOG_COMMENTS_TAB');
$arParams['MESS_SHOW_MAX_QUANTITY'] = $arParams['MESS_SHOW_MAX_QUANTITY'] ?: Loc::getMessage('CT_BCE_CATALOG_SHOW_MAX_QUANTITY');
$arParams['MESS_RELATIVE_QUANTITY_MANY'] = $arParams['MESS_RELATIVE_QUANTITY_MANY'] ?: Loc::getMessage('CT_BCE_CATALOG_RELATIVE_QUANTITY_MANY');
$arParams['MESS_RELATIVE_QUANTITY_FEW'] = $arParams['MESS_RELATIVE_QUANTITY_FEW'] ?: Loc::getMessage('CT_BCE_CATALOG_RELATIVE_QUANTITY_FEW');

$positionClassMap = array(
	'left' => 'product-item-label-left',
	'center' => 'product-item-label-center',
	'right' => 'product-item-label-right',
	'bottom' => 'product-item-label-bottom',
	'middle' => 'product-item-label-middle',
	'top' => 'product-item-label-top'
);

$discountPositionClass = 'product-item-label-big';
if ($arParams['SHOW_DISCOUNT_PERCENT'] === 'Y' && !empty($arParams['DISCOUNT_PERCENT_POSITION']))
{
	foreach (explode('-', $arParams['DISCOUNT_PERCENT_POSITION']) as $pos)
	{
		$discountPositionClass .= isset($positionClassMap[$pos]) ? ' '.$positionClassMap[$pos] : '';
	}
}

$labelPositionClass = 'product-item-label-big';
if (!empty($arParams['LABEL_PROP_POSITION']))
{
	foreach (explode('-', $arParams['LABEL_PROP_POSITION']) as $pos)
	{
		$labelPositionClass .= isset($positionClassMap[$pos]) ? ' '.$positionClassMap[$pos] : '';
	}
}

$themeClass = isset($arParams['TEMPLATE_THEME']) ? ' bx-'.$arParams['TEMPLATE_THEME'] : '';

?>
<section class="product">
  <div class="container product__container">
    <div class="product__block">
      <h1 class="product__head"><?=$name?></h1>
      <div class="product__flex">
        <div class="product__top">
          <div class="product__slider">
            <div class="product__thumbs_swiper swiper-container">
              <ul class="product__thumbs swiper-wrapper">
                <?if($arResult['PROPERTIES']['MORE_PHOTO']["VALUE"]!==false):?>
                  <?foreach($arResult["PROPERTIES"]["MORE_PHOTO"]["VALUE"] as $value):?>
                    <div class="product__thumbs_image swiper-slide">
                      <img class="lazyloaded" src="<?=CFile::GetPath($value)?>" alt="" title="">
                    </div>
                  <?endforeach;?>
                <?else:?>
                  <div class="product__thumbs_image swiper-slide">
                    <img class="lazyloaded" src="<?=$arResult['DETAIL_PICTURE']['SRC']?>" alt="" title="">
                  </div>
                <?endif;?>
              </ul>
            </div>
            <div class="product__swiper swiper-container">
              <ul class="product__big swiper-wrapper">
              <?if($arResult['PROPERTIES']['MORE_PHOTO']["VALUE"]!==false):?>
                <?foreach($arResult["PROPERTIES"]["MORE_PHOTO"]["VALUE"] as $value):?>
                  <div class="product__image swiper-slide">
                    <img class="lazyloaded" src="<?=CFile::GetPath($value)?>" alt="" title="">
                  </div>
                <?endforeach;?>
              <?else:?>
                <div class="product__image swiper-slide">
                  <img class="lazyloaded" src="<?=$arResult['DETAIL_PICTURE']['SRC']?>" alt="" title="">
                </div>
              <?endif;?>
              </ul>
            </div>
          </div>
          <div class="product__order">
            <div class="product__prices">
              <span class="product__price"><?=$arResult["ITEM_PRICES"][0]["PRINT_BASE_PRICE"]?></span>
              <button id="<?=$arResult["ID"]?>" class="products__favorites product__favorites <?=(isProductInWishList($arResult["ID"])) ? 'added' : '';?>">
                <svg viewBox="467 392 58 57" xmlns="http://www.w3.org/2000/svg">
                  <g fill="none" fill-rule="evenodd" transform="translate(467 392)">
                    <path d="M29.144 20.773c-.063-.13-4.227-8.67-11.44-2.59C7.63 28.795 28.94 43.256 29.143 43.394c.204-.138 21.513-14.6 11.44-25.213-7.214-6.08-11.377 2.46-11.44 2.59z" class="favorites__heart" fill="currentColor"/>
                    <circle class="favorites__circle" fill="#E2264D" opacity="0" cx="29.5" cy="29.5" r="1.5"/>
                    <g class="favorites__seven" opacity="0" transform="translate(7 6)">
                      <circle class="favorites__first" fill="#9CD8C3" cx="2" cy="6" r="2"/>
                      <circle class="favorites__second" fill="#8CE8C3" cx="5" cy="2" r="2"/>
                    </g>
                    <g class="favorites__six" opacity="0" transform="translate(0 28)">
                      <circle class="favorites__first" fill="#CC8EF5" cx="2" cy="7" r="2"/>
                      <circle class="favorites__second" fill="#91D2FA" cx="3" cy="2" r="2"/>
                    </g>
                    <g class="favorites__three" opacity="0" transform="translate(52 28)">
                      <circle class="favorites__second" fill="#9CD8C3" cx="2" cy="7" r="2"/>
                      <circle class="favorites__first" fill="#8CE8C3" cx="4" cy="2" r="2"/>
                    </g>
                    <g class="favorites__two" opacity="0" transform="translate(44 6)">
                      <circle class="favorites__second" fill="#CC8EF5" cx="5" cy="6" r="2"/>
                      <circle class="favorites__first" fill="#CC8EF5" cx="2" cy="2" r="2"/>
                    </g>
                    <g class="favorites__five" opacity="0" transform="translate(14 50)">
                      <circle class="favorites__first" fill="#91D2FA" cx="6" cy="5" r="2"/>
                      <circle class="favorites__second" fill="#91D2FA" cx="2" cy="2" r="2"/>
                    </g>
                    <g class="favorites__four" opacity="0" transform="translate(35 50)">
                      <circle class="favorites__first" fill="#F48EA7" cx="6" cy="5" r="2"/>
                      <circle class="favorites__second" fill="#F48EA7" cx="2" cy="2" r="2"/>
                    </g>
                    <g class="favorites__one" opacity="0" transform="translate(24)">
                      <circle class="favorites__first" fill="#9FC7FA" cx="2.5" cy="3" r="2"/>
                      <circle class="favorites__second" fill="#9FC7FA" cx="7.5" cy="2" r="2"/>
                    </g>
                  </g>
                </svg>
              </button>
            </div>
            <div class="product__stocks">
              <?if($actualItem['CAN_BUY']):?>
                <p class="product__stock stock">Есть в наличии</p>
              <?else:?>
                <p class="product__stock">Нет в наличии</p>
              <?endif;?>
              <p class="product__article">Артикул: <?=$arResult['PROPERTIES']['CML2_ARTICLE']['VALUE']?></p>
            </div>
            <div class="product__orders">
              <div class="product__buttons">
                <div class="product__count">
                  <span class="product__minus">-</span>
                  <div class="product__input">

                    <input type="text" id="quantity<?=$actualItem["ID"]?>" class="text" maxlength="18" max="<?=$actualItem["PRODUCT"]["QUANTITY"]?>" min="0" step="1" value="<?=$actualItem['PRODUCT']['QUANTITY']>0 ? 1 : 0?>">
                  </div>
                  <span class="product__plus">+</span>
                </div>
                <button id="<?=$actualItem['ID']?>" class="btn product__btn <?= isProductInBasket($actualItem["ID"]) ? "added" : ""?>" <?=$actualItem['CAN_BUY'] ? "" : "disabled"?>>
                  <span>
                    <svg width="18" height="20" viewBox="0 0 18 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M12.5305 9.96186C12.9852 10.541 13.1915 11.0911 13.2217 11.8049C13.2249 11.8789 13.2205 11.9447 13.2009 12.1147C13.1278 12.7495 12.772 13.5574 12.2334 13.9146C11.9604 14.0954 11.6324 14.2187 11.3189 14.2573C11.119 14.2826 11.003 14.2877 10.9096 14.2769C10.88 14.2731 10.8169 14.2662 10.7702 14.2611C10.1787 14.1947 9.63374 13.8754 9.26731 13.381C9.24145 13.3449 9.21433 13.3076 9.20865 13.2969C9.19415 13.2729 9.18027 13.2729 9.16577 13.2969C9.12036 13.3753 8.9898 13.5334 8.88448 13.6383C8.53886 13.9848 8.08098 14.2074 7.60418 14.2611C7.55751 14.2662 7.49444 14.2731 7.4648 14.2769C7.40299 14.2839 7.30208 14.2839 7.23207 14.2763C7.20622 14.2737 7.15198 14.2681 7.11224 14.2643C6.26396 14.1776 5.52984 13.3502 5.26495 12.5333C5.20693 12.3531 5.18296 12.2273 5.15647 11.9567C5.1426 11.8113 5.16846 11.4793 5.21134 11.2549C5.29995 10.7931 5.48526 10.3834 5.80397 9.96186" stroke="currentColor"></path>
                      <path d="M1.20111 16.8171L2.04422 7.66325C2.13901 6.6341 3.0023 5.84668 4.03579 5.84668H13.9344C14.948 5.84668 15.8013 6.60492 15.9205 7.61148L17.0045 16.7653C17.1454 17.9548 16.2162 19.0005 15.0184 19.0005H3.19268C2.01567 19.0005 1.09315 17.9891 1.20111 16.8171Z" stroke="currentColor"></path>
                      <path d="M11 4C11 2.75736 10.2426 1 9 1C7.75736 1 7 2.75736 7 4" stroke="currentColor"></path>
                    </svg>
                    <div class="products__inner"><?= isProductInBasket($actualItem["ID"]) ? "Добавлен" : "В корзину"?></div>
                  </span>
                  <span></span>
                </button>
              </div>
              <div class="product__info">
                <p class="product__desc"><?=(!empty($arResult["PROPERTIES"]['DELIVERY']["~VALUE"])) ? $arResult["PROPERTIES"]['DELIVERY']["~VALUE"]["TEXT"] : ''?></p>
                <p class="product__desc">Самовывоз - бесплатно</p>
                <p class="product__desc">Цена действительна только для интернет-магазина и может отличаться от цен в розничных магазинах</p>
              </div>
            </div>
          </div>
        </div>
        <div class="product__bottom">
          <div class="product__blocks">
            <div class="product__blocks_buttons">
              <button class="product__button product__descr_button active">Описание</button>
              <button class="product__button product__character_button ">Характеристики</button>
              <button class="product__button product__delivery_button">Оплата и доставка</button>
              <button class="product__button product__reviews_button">Отзывы</button>
            </div>
            <div class="product__informations">
              <div class="product__informations_blocks">
                <div class="product__information product__descr active">
                  <h4 class="product__information_head">Описание товара</h4>
                  <p class="product__information_desc">
                    <?if($arResult["~DETAIL_TEXT"]!=null):?>
                      <?=$arResult["~DETAIL_TEXT"]?>
                    <?else:?>
                      <?=$arResult["~PREVIEW_TEXT"]?>
                    <?endif;?>
                  </p>
                </div>
                <div class="product__information product__character">
                  <h4 class="product__information_head">Характеристики</h4>
                  <ul class="product__information_list">
                    <?foreach($arResult["PROPERTIES"] as $key => $value):?>
                      <?$bannedProps = ['CML2_ARTICLE', 'CML2_BASE_UNIT', 'MORE_PHOTO', 'CML2_TRAITS', 'CML2_TAXES', 'CML2_BAR_CODE', 'FILES', 'CML2_ATTRIBUTES', 'BRAND', 'DELIVERY', "RECOMMENDED"];
                        if(in_array($key, $bannedProps)) continue;
                        if($value['~VALUE']=="") continue;?>
                      <li class="product__information_item">

                        <p><?=$value["~NAME"]?></p>
                        

                        <p><?=$value['~VALUE']?></p>

                      </li>
                    <?endforeach;?>
                  </ul>
                </div>
                <div class="product__information product__delivery">
                  <h4 class="product__information_head">Оплата и доставка</h4>
                  <p class="product__information_desc">
                    <?=CIBlock::GetArrayByID($arResult["IBLOCK_ID"], "DESCRIPTION");?>
                  </p>
                </div>
              </div>
              <div class="product__information product__reviews">
                <div class="product__reviews_flex">
                  <div class="product__reviews_top">
                    <h4 class="product__information_head">Отзывы</h4>
                    <button class="product__information_button">Оставить отзыв</button>
                  </div>
                  <div class="product__reviews_block active">
                    <div class="product__review_flex">
                      <!-- показать если нет отзывов -->
                      <?if(!$arResult["COMMENTS"]){?> 
                      <div class="product__review">
                        <p class="product__review_clear">
                          Помогите другим пользователям с выбором - будьте первым, кто поделится своим мнением об этом товаре
                        </p>
                      </div>
                      <?} else{?>
                      <!-- показать если есть отзывы -->
                      <?foreach($arResult["COMMENTS"] as $value):?>
                        <? $liked = false;
                        if(is_array($value["PROPERTY_LIKED_BY_VALUE"])){
                          if($USER->IsAuthorized()){
                            $liked = in_array($USER->GetID(), $value["PROPERTY_LIKED_BY_VALUE"]);
                          }
                          else{
                            $liked = in_array($_COOKIE['BX_USER_ID'], $value["PROPERTY_LIKED_BY_VALUE"]);
                          }
                        }
                        else{
                          if($value["PROPERTY_LIKED_BY_VALUE"]==$USER->GetID() || $value["PROPERTY_LIKED_BY_VALUE"]==$_COOKIE['BX_USER_ID']){
                            $liked = true;
                          }
                        } ?>
                      <div class="product__review">
                        <div class="product__review_top">
                          <div class="product__review_left">
                              <div class="product__review_image">
                                <img src="<?=SITE_TEMPLATE_PATH.'/img/user/userid_1.svg'?>" alt="">
                              </div>
                            <h5 id="commentname<?=$value["ID"]?>" class="product__review_name"><?=$value["NAME"]?></h5>
                            <p class="product__review_date"><?=substr($value["DATE_CREATE"],0,-9)?></p>
                          </div>
                          <div class="product__review_right">
                            <span class="product__review_count"><?=(is_null($value["PROPERTY_LIKES_VALUE"])) ? '0' : $value["PROPERTY_LIKES_VALUE"]?></span>
                            <button id="<?=$value["ID"]?>" class="product__review_like <?=($liked) ? 'liked' : ''?>">
                              <svg width="155" height="155" viewBox="0 0 155 155" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M129.167 0H25.8333C11.5389 0 0 10.3139 0 23.0319V107.637C0 120.355 11.5389 130.669 25.8333 130.669H32.3778C39.2667 130.669 45.8111 133.073 50.6333 137.416L65.3583 150.522C72.075 156.493 83.0111 156.493 89.7278 150.522L104.453 137.416C109.275 133.073 115.906 130.669 122.708 130.669H129.167C143.461 130.669 155 120.355 155 107.637V23.0319C155 10.3139 143.461 0 129.167 0ZM79.9111 100.503C78.6194 100.891 76.4667 100.891 75.0889 100.503C63.8944 97.0131 38.75 82.6667 38.75 58.2389C38.8361 47.4597 48.3944 38.7742 60.2778 38.7742C67.3389 38.7742 73.5389 41.7986 77.5 46.5291C81.4611 41.7986 87.6611 38.7742 94.7222 38.7742C106.606 38.7742 116.25 47.4597 116.25 58.2389C116.164 82.6667 91.1055 97.0131 79.9111 100.503Z" fill="currentColor"/>
                              </svg>
                            </button>
                          </div>
                        </div>
                        <div class="product__review_text">
                          <div class="product__review_block">
                            <span class="product__review_head">Достоинства</span>
                            <p id="dignity<?=$value["ID"]?>" class="product__review_desc"><?=$value["PROPERTY_DIGNITY_VALUE"]?></p>
                          </div>
                          <div class="product__review_block">
                            <span class="product__review_head">Недостатки</span>
                            <p id="flaws<?=$value["ID"]?>" class="product__review_desc"><?=$value["PROPERTY_FLAWS_VALUE"]?></p>
                          </div>
                          <div class="product__review_block">
                            <span class="product__review_head">Комментарий</span>
                            <p id="comment<?=$value["ID"]?>" class="product__review_desc"><?=$value["PROPERTY_COMMENT_VALUE"]?></p>
                          </div>
                          <?if($USER->GetID()==$value["MODIFIED_BY"] || $USER->GetID()=='1' || $_COOKIE['BX_USER_ID']===$value['PROPERTY_CREATED_VALUE']):?>
                          <div class="product__review_buttons">
                            <button id="<?=$value["ID"]?>" class="product__review_button product__review_edit">Редактировать</button>
                            <button id="<?=$value["ID"]?>" class="product__review_button product__review_del">Удалить</button>
                          </div>
                          <?endif;?>
                        </div>
                      </div>
                      <?endforeach;}?>
                    </div>
                  </div>
                  <div class="product__reviews_forms">
                    <form class="product__reviews_form">
                      <div class="product__reviews_form_top">
                        <label class="product__reviews_label">
                          Ваше имя*
                          <input type="text" name="name" class="product__reviews_input" required>
                          <input type="hidden" name="userid" value=<?=$USER->GetID()?>>
                          <input type="hidden" name="edit" value='false'>
                          <input type="hidden" name="productname" value="<?=$arResult['~NAME']?>">
                          <input type="hidden" name="productid" value=<?=$arResult['ID']?>>
                        </label>
                        <label class="product__reviews_label">
                          E-mail
                          <input type="email" name="email" class="product__reviews_input">
                        </label>
                      </div>
                      <label class="product__reviews_label">
                        Достоинства
                        <input type="text" name="dignity" class="product__reviews_input">
                      </label>
                      <label class="product__reviews_label">
                        Недостатки
                        <input type="text" name="flaws" class="product__reviews_input">
                      </label>
                      <label class="product__reviews_label">
                        Комментарий
                        <input type="text" name="comment" class="product__reviews_input">
                      </label>
                      <button type="submit" class="btn product__reviews_btn">
                        <span>
                          Опубликовать
                        </span>
                        <span></span>
                      </button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="product__recommended">
            <h2 class="product__recommended_head">Рекомендации для Вас</h2>
            <ul class="product__recommended_list">
              <?foreach($arResult["PROPERTIES"]["RECOMMENDED"]["VALUE"] as $key => $value):?>
                <li class="product__recommended_item">
                  <a href="<?=$value["DETAIL_PAGE_URL"]?>" class="product__recommended_link">
                    <div class="product__recommended_image">
                        <img src="<?=($value['PROPERTY_MORE_PHOTO_VALUE']!==NULL) ? CFile::GetPath($value['PROPERTY_MORE_PHOTO_VALUE']) : CFile::GetPath($value["DETAIL_PICTURE"])?>" alt="">
                    </div>
                    <div class="product__recommended_info">
                      <p class="product__recommended_name"><?=$value["NAME"]?></p>
                      <span class="product__recommended_count"><?=$value['QUANTITY']?> шт</span>
                    </div>
                    <span class="product__recommended_price"><?=substr($value["PRICE_1"], 0, -3)?> ₽</span>
                  </a>
                </li>
              <?endforeach;?>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>



<script>
  $('.product__reviews').on('click', '.product__review_del', function(){
    $.ajax({
      url: '/local/ajax/comment.php',
      method: 'post',
      dataType: 'html',
      data: {
        type: 'del',
        id: $(this).attr('id')  
      },
      success: function(data){
        $.ajax({
          url: window.location.href,
          method: 'GET',
          dataType: 'html',
          success: function(data){
            let newComments = $(data).find('.product__review_flex').html();
            $('.product__review_flex').html(newComments)
          }
        });
      }
    });
  })
  $('.product__reviews').on('click','.product__review_edit', function(){
    let that = $(this);
    let id = that.attr('id');
    $("input[name=edit]").val(id);
    let name = $('#commentname' + id).html();
    let dignity = $('#dignity' + id).html();
    let flaws = $('#flaws' + id).html();
    let comment = $('#comment' + id).html();
    $('input[name=email]').removeAttr('required').attr('disabled', true);
    $('input[name=name]').val(name);
    $('input[name=dignity]').val(dignity);
    $('input[name=flaws]').val(flaws);
    $('input[name=comment]').val(comment);
    $('.product__information_button').click();
  })
  $('.product__reviews').on('click', '.product__review_like', function(){
    let that = $(this);
    $.ajax({
      url: '/local/ajax/comment.php',
      method: 'post',
      dataType: 'html',
      data: {
        type: 'like',
        liked: $(this).hasClass('liked'),
        id: $(this).attr('id'),
        userid: <?=$USER->GetID()?>
      },
      success: function(data){
        that.prev().html(data);
      }
    });
    if($(this).hasClass('liked')){
      $(this).removeClass('liked');
    }
    else{
      $(this).addClass('liked');
    }
  })

  $('.product__reviews').on('submit','.product__reviews_form', function(e){
    e.preventDefault();
    var data = $(this).serialize();
    $.ajax({
      url: '/local/ajax/comment.php',
      method: 'post',
      dataType: 'html',
      data: data,
      success: function(e){
        $.ajax({
          url: window.location.href,
          method: 'GET',
          dataType: 'html',
          success: function(data){
            let newComments = $(data).find('.product__review_flex').html();
            $('.product__review_flex').html(newComments);
            $('.product__information_button').click();
            $('.product__reviews_form').trigger('reset');
            $('.product__reviews_form input[name="edit"]').val(false);
          }
        });
      }
    });
  })
</script>








 

		

	<meta itemprop="name" content="<?=$name?>" />
	<meta itemprop="category" content="<?=$arResult['CATEGORY_PATH']?>" />
	<meta itemprop="id" content="<?=$arResult['ID']?>" />
	




	<?

		$jsParams = array(
			'CONFIG' => array(
				'USE_CATALOG' => $arResult['CATALOG'],
				'SHOW_QUANTITY' => $arParams['USE_PRODUCT_QUANTITY'],
				'SHOW_PRICE' => !empty($arResult['ITEM_PRICES']),
				'SHOW_DISCOUNT_PERCENT' => $arParams['SHOW_DISCOUNT_PERCENT'] === 'Y',
				'SHOW_OLD_PRICE' => $arParams['SHOW_OLD_PRICE'] === 'Y',
				'USE_PRICE_COUNT' => $arParams['USE_PRICE_COUNT'],
				'DISPLAY_COMPARE' => $arParams['DISPLAY_COMPARE'],
				'MAIN_PICTURE_MODE' => $arParams['DETAIL_PICTURE_MODE'],
				'ADD_TO_BASKET_ACTION' => $arParams['ADD_TO_BASKET_ACTION'],
				'SHOW_CLOSE_POPUP' => $arParams['SHOW_CLOSE_POPUP'] === 'Y',
				'SHOW_MAX_QUANTITY' => $arParams['SHOW_MAX_QUANTITY'],
				'RELATIVE_QUANTITY_FACTOR' => $arParams['RELATIVE_QUANTITY_FACTOR'],
				'TEMPLATE_THEME' => $arParams['TEMPLATE_THEME'],
				'USE_STICKERS' => true,
				'USE_SUBSCRIBE' => $showSubscribe,
				'SHOW_SLIDER' => $arParams['SHOW_SLIDER'],
				'SLIDER_INTERVAL' => $arParams['SLIDER_INTERVAL'],
				'ALT' => $alt,
				'TITLE' => $title,
				'MAGNIFIER_ZOOM_PERCENT' => 200,
				'USE_ENHANCED_ECOMMERCE' => $arParams['USE_ENHANCED_ECOMMERCE'],
				'DATA_LAYER_NAME' => $arParams['DATA_LAYER_NAME'],
				'BRAND_PROPERTY' => !empty($arResult['DISPLAY_PROPERTIES'][$arParams['BRAND_PROPERTY']])
					? $arResult['DISPLAY_PROPERTIES'][$arParams['BRAND_PROPERTY']]['DISPLAY_VALUE']
					: null
			),
			'VISUAL' => $itemIds,
			'PRODUCT_TYPE' => $arResult['PRODUCT']['TYPE'],
			'PRODUCT' => array(
				'ID' => $arResult['ID'],
				'ACTIVE' => $arResult['ACTIVE'],
				'PICT' => reset($arResult['MORE_PHOTO']),
				'NAME' => $arResult['~NAME'],
				'SUBSCRIPTION' => true,
				'ITEM_PRICE_MODE' => $arResult['ITEM_PRICE_MODE'],
				'ITEM_PRICES' => $arResult['ITEM_PRICES'],
				'ITEM_PRICE_SELECTED' => $arResult['ITEM_PRICE_SELECTED'],
				'ITEM_QUANTITY_RANGES' => $arResult['ITEM_QUANTITY_RANGES'],
				'ITEM_QUANTITY_RANGE_SELECTED' => $arResult['ITEM_QUANTITY_RANGE_SELECTED'],
				'ITEM_MEASURE_RATIOS' => $arResult['ITEM_MEASURE_RATIOS'],
				'ITEM_MEASURE_RATIO_SELECTED' => $arResult['ITEM_MEASURE_RATIO_SELECTED'],
				'SLIDER_COUNT' => $arResult['MORE_PHOTO_COUNT'],
				'SLIDER' => $arResult['MORE_PHOTO'],
				'CAN_BUY' => $arResult['CAN_BUY'],
				'CHECK_QUANTITY' => $arResult['CHECK_QUANTITY'],
				'QUANTITY_FLOAT' => is_float($arResult['ITEM_MEASURE_RATIOS'][$arResult['ITEM_MEASURE_RATIO_SELECTED']]['RATIO']),
				'MAX_QUANTITY' => $arResult['PRODUCT']['QUANTITY'],
				'STEP_QUANTITY' => $arResult['ITEM_MEASURE_RATIOS'][$arResult['ITEM_MEASURE_RATIO_SELECTED']]['RATIO'],
				'CATEGORY' => $arResult['CATEGORY_PATH']
			),
			'BASKET' => array(
				'ADD_PROPS' => $arParams['ADD_PROPERTIES_TO_BASKET'] === 'Y',
				'QUANTITY' => $arParams['PRODUCT_QUANTITY_VARIABLE'],
				'PROPS' => $arParams['PRODUCT_PROPS_VARIABLE'],
				'EMPTY_PROPS' => $emptyProductProperties,
				'BASKET_URL' => $arParams['BASKET_URL'],
				'ADD_URL_TEMPLATE' => $arResult['~ADD_URL_TEMPLATE'],
				'BUY_URL_TEMPLATE' => $arResult['~BUY_URL_TEMPLATE']
			)
		);
		unset($emptyProductProperties);



	?>
</div>

<script type="text/javascript">
var viewedCounter = {
    path: '/bitrix/components/bitrix/catalog.element/ajax.php',
    params: {
        AJAX: 'Y',
        SITE_ID: "<?= SITE_ID ?>",
        PRODUCT_ID: "<?= $arResult['ID'] ?>",
        PARENT_ID: "<?= $arResult['ID'] ?>"
    }
};
BX.ready(
    BX.defer(function(){
        BX.ajax.post(
            viewedCounter.path,
            viewedCounter.params
        );
    })
);
</script>
<script>
	BX.message({
		ECONOMY_INFO_MESSAGE: '<?=GetMessageJS('CT_BCE_CATALOG_ECONOMY_INFO2')?>',
		TITLE_ERROR: '<?=GetMessageJS('CT_BCE_CATALOG_TITLE_ERROR')?>',
		TITLE_BASKET_PROPS: '<?=GetMessageJS('CT_BCE_CATALOG_TITLE_BASKET_PROPS')?>',
		BASKET_UNKNOWN_ERROR: '<?=GetMessageJS('CT_BCE_CATALOG_BASKET_UNKNOWN_ERROR')?>',
		BTN_SEND_PROPS: '<?=GetMessageJS('CT_BCE_CATALOG_BTN_SEND_PROPS')?>',
		BTN_MESSAGE_DETAIL_BASKET_REDIRECT: '<?=GetMessageJS('CT_BCE_CATALOG_BTN_MESSAGE_BASKET_REDIRECT')?>',
		BTN_MESSAGE_CLOSE: '<?=GetMessageJS('CT_BCE_CATALOG_BTN_MESSAGE_CLOSE')?>',
		BTN_MESSAGE_DETAIL_CLOSE_POPUP: '<?=GetMessageJS('CT_BCE_CATALOG_BTN_MESSAGE_CLOSE_POPUP')?>',
		TITLE_SUCCESSFUL: '<?=GetMessageJS('CT_BCE_CATALOG_ADD_TO_BASKET_OK')?>',
		COMPARE_MESSAGE_OK: '<?=GetMessageJS('CT_BCE_CATALOG_MESS_COMPARE_OK')?>',
		COMPARE_UNKNOWN_ERROR: '<?=GetMessageJS('CT_BCE_CATALOG_MESS_COMPARE_UNKNOWN_ERROR')?>',
		COMPARE_TITLE: '<?=GetMessageJS('CT_BCE_CATALOG_MESS_COMPARE_TITLE')?>',
		BTN_MESSAGE_COMPARE_REDIRECT: '<?=GetMessageJS('CT_BCE_CATALOG_BTN_MESSAGE_COMPARE_REDIRECT')?>',
		PRODUCT_GIFT_LABEL: '<?=GetMessageJS('CT_BCE_CATALOG_PRODUCT_GIFT_LABEL')?>',
		PRICE_TOTAL_PREFIX: '<?=GetMessageJS('CT_BCE_CATALOG_MESS_PRICE_TOTAL_PREFIX')?>',
		RELATIVE_QUANTITY_MANY: '<?=CUtil::JSEscape($arParams['MESS_RELATIVE_QUANTITY_MANY'])?>',
		RELATIVE_QUANTITY_FEW: '<?=CUtil::JSEscape($arParams['MESS_RELATIVE_QUANTITY_FEW'])?>',
		SITE_ID: '<?=CUtil::JSEscape($component->getSiteId())?>'
	});

	var <?=$obName?> = new JCCatalogElement(<?=CUtil::PhpToJSObject($jsParams, false, true)?>);
</script>
<?php
unset($actualItem, $itemIds, $jsParams);?>




<?$APPLICATION->IncludeComponent(
	"bitrix:catalog.products.viewed",
	"template1",
	Array(
		"ACTION_VARIABLE" => "action_cpv",
		"ADDITIONAL_PICT_PROP_4" => "IMAGES",
		"ADDITIONAL_PICT_PROP_5" => "-",
		"ADD_PROPERTIES_TO_BASKET" => "Y",
		"ADD_TO_BASKET_ACTION" => "ADD",
		"BASKET_URL" => "/personal/basket.php",
		"CACHE_GROUPS" => "Y",
		"CACHE_TIME" => "3600",
		"CACHE_TYPE" => "A",
		"COMPONENT_TEMPLATE" => "template1",
		"CONVERT_CURRENCY" => "N",
		"DEPTH" => "2",
		"DISPLAY_COMPARE" => "N",
		"ENLARGE_PRODUCT" => "STRICT",
		"HIDE_NOT_AVAILABLE" => "N",
		"HIDE_NOT_AVAILABLE_OFFERS" => "N",
		"IBLOCK_ID" => "11",
		"IBLOCK_MODE" => "single",
		"IBLOCK_TYPE" => "catalog",
		"LABEL_PROP_4" => array(),
		"LABEL_PROP_POSITION" => "top-left",
		"MESS_BTN_ADD_TO_BASKET" => "В корзину",
		"MESS_BTN_BUY" => "Купить",
		"MESS_BTN_DETAIL" => "Подробнее",
		"MESS_BTN_SUBSCRIBE" => "Подписаться",
		"MESS_NOT_AVAILABLE" => "Нет в наличии",
		"PAGE_ELEMENT_COUNT" => "9",
		"PARTIAL_PRODUCT_PROPERTIES" => "N",
		"PRICE_CODE" => array(0=>"Розничная цена",),
		"PRICE_VAT_INCLUDE" => "Y",
		"PRODUCT_BLOCKS_ORDER" => "price,props,sku,quantityLimit,quantity,buttons",
		"PRODUCT_ID_VARIABLE" => "id",
		"PRODUCT_PROPS_VARIABLE" => "prop",
		"PRODUCT_QUANTITY_VARIABLE" => "quantity",
		"PRODUCT_ROW_VARIANTS" => "[{'VARIANT':'2','BIG_DATA':false},{'VARIANT':'2','BIG_DATA':false},{'VARIANT':'2','BIG_DATA':false}]",
		"PRODUCT_SUBSCRIPTION" => "N",
		"SECTION_CODE" => "",
		"SECTION_ELEMENT_CODE" => "",
		"SECTION_ELEMENT_ID" => $GLOBALS["CATALOG_CURRENT_ELEMENT_ID"],
		"SECTION_ID" => $GLOBALS["CATALOG_CURRENT_SECTION_ID"],
		"SHOW_CLOSE_POPUP" => "N",
		"SHOW_DISCOUNT_PERCENT" => "N",
		"SHOW_FROM_SECTION" => "N",
		"SHOW_MAX_QUANTITY" => "N",
		"SHOW_OLD_PRICE" => "N",
		"SHOW_PRICE_COUNT" => "1",
		"SHOW_SLIDER" => "Y",
		"SLIDER_INTERVAL" => "3000",
		"SLIDER_PROGRESS" => "N",
		"TEMPLATE_THEME" => "blue",
		"USE_ENHANCED_ECOMMERCE" => "N",
		"USE_PRICE_COUNT" => "N",
		"USE_PRODUCT_QUANTITY" => "N"
	)
);?>

