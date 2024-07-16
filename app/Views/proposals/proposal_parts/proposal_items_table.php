<?php
$color = get_setting("proposal_color");
if (!$color) {
    $color = get_setting("invoice_color") ? get_setting("invoice_color") : "#2AA384";
}

$discount_row = '<tr>
                        <td colspan="3" style="text-align: right;">' . app_lang("discount") . '</td>
                        <td style="text-align: right; width: 20%; border: 1px solid #fff; background-color: #f4f4f4;">' . to_currency($proposal_total_summary->discount_total, $proposal_total_summary->currency_symbol) . '</td>
                    </tr>';

$total_after_discount_row = '<tr>
                                    <td colspan="3" style="text-align: right;">' . app_lang("total_after_discount") . '</td>
                                    <td style="text-align: right; width: 20%; border: 1px solid #fff; background-color: #f4f4f4;">' . to_currency($proposal_total_summary->proposal_subtotal - $proposal_total_summary->discount_total, $proposal_total_summary->currency_symbol) . '</td>
                                </tr>';
?>

<table class="table-responsive" style="width: 100%; color: #444;">            
    <tr style="font-weight: bold; background-color: <?php echo $color; ?>; color: #fff;  ">
        <th colspan="2" style="width: 45%; border-right: 1px solid #eee;"> <?php echo app_lang("item"); ?> </th>
        <!--<th style="text-align: right;  width: 20%; border-right: 1px solid #eee;"> <?php //echo app_lang("rate"); ?></th>-->
        <th style="text-align: center;  width: 15%; border-right: 1px solid #eee;"> <?php echo app_lang("quantity"); ?></th>
        <th style="text-align: right;  width: 20%; "> <?php echo app_lang("total"); ?></th>
    </tr>
    <?php
    foreach ($proposal_items as $item) {
        ?>
        <tr style="background-color: #f4f4f4; ">
            <td colspan="2" style="width: 45%; border: 1px solid #fff; padding: 10px;"><?php echo $item->title; ?>
                <br />
                <span style="color: #888; font-size: 90%;"><?php echo nl2br($item->description ? process_images_from_content($item->description) : ""); ?></span>
            </td>
            <!--<td style="text-align: right; width: 20%; border: 1px solid #fff;"> <?php echo to_currency($item->rate, $item->currency_symbol); ?></td>-->
            <td style="text-align: center; width: 15%; border: 1px solid #fff;"> <?php echo $item->quantity + $item->quantity_gp  . " " . $item->unit_type; ?></td>
            <td style="text-align: right; width: 20%; border: 1px solid #fff;"> <?php echo to_currency($item->total, $item->currency_symbol); ?></td>
        </tr>
    <?php } ?>
    <tr>
        <td style="text-align: right;"><?php echo app_lang("sub_total"); ?></td>
        <td style="text-align: right; width: 20%; border: 1px solid #fff; background-color: #f4f4f4;">
            <?php echo $proposal_total_summary->proposal_total_quantity; ?>
        </td>
        <td colspan="2" style="text-align: right; width: 20%; border: 1px solid #fff; background-color: #f4f4f4;">
            <?php echo to_currency($proposal_total_summary->proposal_subtotal, $proposal_total_summary->currency_symbol); ?>
        </td>
    </tr>
    <?php
    if ($proposal_total_summary->discount_total && $proposal_total_summary->discount_type == "before_tax") {
        echo $discount_row . $total_after_discount_row;
    }
    ?>  
    <?php if ($proposal_total_summary->tax) { ?>
        <tr>
            <td colspan="2" style="text-align: right;"><?php echo $proposal_total_summary->tax_name; ?></td>
            <td colspan="2" style="text-align: right; width: 20%; border: 1px solid #fff; background-color: #f4f4f4;">
                <?php echo to_currency($proposal_total_summary->tax, $proposal_total_summary->currency_symbol); ?>
            </td>
        </tr>
    <?php } ?>
    <?php if ($proposal_total_summary->tax2) { ?>
        <tr>
            <td colspan="2" style="text-align: right;"><?php echo $proposal_total_summary->tax_name2; ?></td>
            <td colspan="2" style="text-align: right; width: 20%; border: 1px solid #fff; background-color: #f4f4f4;">
                <?php echo to_currency($proposal_total_summary->tax2, $proposal_total_summary->currency_symbol); ?>
            </td>
        </tr>
    <?php } ?>
    <?php
    if ($proposal_total_summary->discount_total && $proposal_total_summary->discount_type == "after_tax") {
        echo $discount_row;
    }
    ?> 
    <tr>
        <td colspan="2" style="text-align: right;"><?php echo app_lang("total"); ?></td>
        <td colspan="2" style="text-align: right; width: 20%; background-color: <?php echo $color; ?>; color: #fff;">
            <?php echo to_currency($proposal_total_summary->proposal_total, $proposal_total_summary->currency_symbol); ?>
        </td>
    </tr>
</table>