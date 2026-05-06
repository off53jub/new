<?php
/**
 * 既存CPT統合用フック。
 *
 * 本プラグインでは新しいCPTを登録しません。
 * - アクター   : 既存CPT `actor`
 * - ニュース   : 既存CPT `news_list`
 * - スライド   : 既存CPT `slides`
 * - Works      : サイト側で運用しているCPT（ショートコードに `post_type` で指定）
 *
 * 既存CPTに対して、本プラグインは `_audubon_*` 名前空間のメタフィールドだけを追加し、
 * 既存のフィールド・表示には影響を与えません。
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * news_list アーカイブを「放映日時（フリーテキスト）」優先で並べる。
 * フリーテキストなので strict な日時比較ではなく文字列の降順扱い。
 * 並び替え用に別メタ `_audubon_broadcast_sort` を任意で使えるようにしてあります。
 */
add_action( 'pre_get_posts', 'audubon_order_news_list' );
function audubon_order_news_list( $query ) {
    if ( is_admin() || ! $query->is_main_query() ) {
        return;
    }
    if ( $query->is_post_type_archive( 'news_list' ) ) {
        // 並び替え用の補助メタを優先、なければ通常の投稿日でソート。
        $query->set( 'meta_key', '_audubon_broadcast_sort' );
        $query->set( 'orderby', array(
            'meta_value' => 'DESC',
            'date'       => 'DESC',
        ) );
    }
}
