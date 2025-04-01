<?php
$color = get_setting("proposal_color");
if (!$color) {
    $color = get_setting("invoice_color") ? get_setting("invoice_color") : "#2AA384";
}

$discount_row = '<tr>
                        <td colspan="4" style="text-align: right;">' . app_lang("discount") . '</td>
                        <td style="text-align: right; width: 15%; border: 1px solid #fff; background-color: #f4f4f4;">' . to_currency($proposal_total_summary->discount_total, $proposal_total_summary->currency_symbol) . '</td>
                    </tr>';

$total_after_discount_row = '<tr>
                                    <td colspan="4" style="text-align: right;">' . app_lang("total_after_discount") . '</td>
                                    <td style="text-align: right; width: 15%; border: 1px solid #fff; background-color: #f4f4f4;">' . to_currency($proposal_total_summary->proposal_subtotal - $proposal_total_summary->discount_total, $proposal_total_summary->currency_symbol) . '</td>
                                </tr>';
?>

<table class="table-responsive" style="width: 100%; color: #444;">            
    <tr style="font-weight: bold; background-color: <?php echo $color; ?>; color: #fff;  ">
        <th style="width: 65%; border-right: 1px solid #eee;"> <?php echo app_lang("item"); ?> </th>
        <!--<th style="text-align: right;  width: 20%; border-right: 1px solid #eee;"> <?php echo app_lang("rate"); ?></th>-->
        <?php if($proposal_total_summary->gp_apart && $proposal_total_summary->qa_apart) : ?>
            <th style="text-align: center;  width: 10%; border-right: 1px solid #eee;"> <?php echo app_lang("quantity"); ?></th>
            <th style="text-align: center;  width: 10%; border-right: 1px solid #eee;"> <?php echo app_lang("quantity_gp"); ?></th>
            <th style="text-align: center;  width: 10%; border-right: 1px solid #eee;"> <?php echo app_lang("quantity_qa"); ?></th>
        <?php elseif($proposal_total_summary->gp_apart) : ?>
            <th style="text-align: center;  width: 10%; border-right: 1px solid #eee;"> <?php echo app_lang("quantity"); ?></th>
            <th colspan="2" style="text-align: center;  width: 10%; border-right: 1px solid #eee;"> <?php echo app_lang("quantity_gp"); ?></th>
        <?php elseif($proposal_total_summary->qa_apart) : ?>
            <th style="text-align: center;  width: 10%; border-right: 1px solid #eee;"> <?php echo app_lang("quantity"); ?></th>
            <th colspan="2" style="text-align: center;  width: 10%; border-right: 1px solid #eee;"> <?php echo app_lang("quantity_qa"); ?></th>
        <?php else: ?>
            <th colspan="3" style="text-align: center;  width: 10%; border-right: 1px solid #eee;"> <?php echo app_lang("quantity"); ?></th>
        <?php endif; ?>

        <th style="text-align: right;  width: 15%; "> <?php echo app_lang("total"); ?></th>
    </tr>
    <?php
    foreach ($proposal_items as $item) {
        ?>
        <tr style="background-color: #f4f4f4; ">
            <td style="width: 65%; border: 1px solid #fff; padding: 10px;"><?php echo $item->title; ?>
                <br />
                <span style="color: #888; font-size: 90%;"><?php echo nl2br($item->description ? process_images_from_content($item->description) : ""); ?></span>
            </td>
            <!--<td style="text-align: right; width: 20%; border: 1px solid #fff;"> <?php echo to_currency($item->rate, $item->currency_symbol); ?></td>-->
            <?php if($proposal_total_summary->gp_apart && $proposal_total_summary->qa_apart) : ?>
                <td style="text-align: center; width: 10%; border: 1px solid #fff;"> <?php echo ($item->quantity + ($item->quantity_add ?? 0)) . " " . $item->unit_type; ?></td>
                <td style="text-align: center; width: 10%; border: 1px solid #fff;"> <?php echo ($item->quantity_gp) . " " . $item->unit_type; ?></td>
                <td style="text-align: center; width: 10%; border: 1px solid #fff;"> <?php echo ($item->quantity_qa) . " " . $item->unit_type; ?></td>
            <?php elseif($proposal_total_summary->gp_apart) : ?>
                <td style="text-align: center; width: 10%; border: 1px solid #fff;"> <?php echo ($item->quantity + $item->quantity_qa + ($item->quantity_add ?? 0)). " " . $item->unit_type; ?></td>
                <td colspan="2" style="text-align: center; width: 10%; border: 1px solid #fff;"> <?php echo ($item->quantity_gp) . " " . $item->unit_type; ?></td>
            <?php elseif($proposal_total_summary->qa_apart) : ?>
                <td style="text-align: center; width: 10%; border: 1px solid #fff;"> <?php echo $item->quantity + $item->quantity_gp + ($item->quantity_add ?? 0) . " " . $item->unit_type; ?></td>
                <td colspan="2" style="text-align: center; width: 10%; border: 1px solid #fff;"> <?php echo ($item->quantity_qa) . " " . $item->unit_type; ?></td>
            <?php else: ?>
                <td colspan="3" style="text-align: center; width: 10%; border: 1px solid #fff;"> <?php echo ($item->quantity + $item->quantity_gp + $item->quantity_qa + ($item->quantity_add ?? 0)) . " " . $item->unit_type; ?></td>
            <?php endif; ?>
            <td style="text-align: right; width: 15%; border: 1px solid #fff;"> <?php echo to_currency($item->total, $item->currency_symbol); ?></td>
        </tr>
    <?php } ?>
    <tr>
        <td style="text-align: right;"><?php echo app_lang("sub_total"); ?></td>
        <?php if($proposal_total_summary->gp_apart && $proposal_total_summary->qa_apart) : ?>
            <td style="text-align: center; width: 20%; border: 1px solid #fff; background-color: #f4f4f4;">
                <?php echo $proposal_total_summary->proposal_total_quantity; ?>
            </td>
            <td style="text-align: center; width: 10%; border: 1px solid #fff; background-color: #f4f4f4;">
                <?php echo $proposal_total_summary->proposal_total_gp_quantity; ?>
            </td>
            <td style="text-align: center; width: 10%; border: 1px solid #fff; background-color: #f4f4f4;">
                <?php echo $proposal_total_summary->proposal_total_qa_quantity; ?>
            </td>
            <td style="text-align: right; width: 15%; border: 1px solid #fff; background-color: #f4f4f4;">
                <?php echo to_currency($proposal_total_summary->proposal_subtotal, $proposal_total_summary->currency_symbol); ?>
            </td>
        <?php elseif($proposal_total_summary->gp_apart) : ?>
            <td style="text-align: center; width: 20%; border: 1px solid #fff; background-color: #f4f4f4;">
                <?php echo $proposal_total_summary->proposal_total_not_gp_sum_quantity; ?>
            </td>
            <td colspan="2" style="text-align: center; width: 10%; border: 1px solid #fff; background-color: #f4f4f4;">
                <?php echo $proposal_total_summary->proposal_total_gp_quantity; ?>
            </td>
            <td style="text-align: right; width: 15%; border: 1px solid #fff; background-color: #f4f4f4;">
                <?php echo to_currency($proposal_total_summary->proposal_subtotal, $proposal_total_summary->currency_symbol); ?>
            </td>
        <?php elseif($proposal_total_summary->qa_apart) : ?>
            <td style="text-align: center; width: 20%; border: 1px solid #fff; background-color: #f4f4f4;">
                <?php echo $proposal_total_summary->proposal_total_not_qa_sum_quantity; ?>
            </td>
            <td colspan="2" style="text-align: center; width: 10%; border: 1px solid #fff; background-color: #f4f4f4;">
                <?php echo $proposal_total_summary->proposal_total_qa_quantity; ?>
            </td>
            <td style="text-align: right; width: 15%; border: 1px solid #fff; background-color: #f4f4f4;">
                <?php echo to_currency($proposal_total_summary->proposal_subtotal, $proposal_total_summary->currency_symbol); ?>
            </td>
        <?php else: ?>
            <td style="text-align: center; width: 20%; border: 1px solid #fff; background-color: #f4f4f4;">
                <?php echo $proposal_total_summary->proposal_total_sum_quantity; ?>
            </td>
            <td colspan="3" style="text-align: right; width: 15%; border: 1px solid #fff; background-color: #f4f4f4;">
                <?php echo to_currency($proposal_total_summary->proposal_subtotal, $proposal_total_summary->currency_symbol); ?>
            </td>
        <?php endif; ?>
    </tr>
    <?php
    if ($proposal_total_summary->discount_total && $proposal_total_summary->discount_type == "before_tax") {
        echo $discount_row . $total_after_discount_row;
    }
    ?>  
    <?php if ($proposal_total_summary->tax) { ?>
        <tr>
            <td colspan="2" style="text-align: right;"><?php echo $proposal_total_summary->tax_name; ?></td>
            <td colspan="3" style="text-align: right; width: 15%; border: 1px solid #fff; background-color: #f4f4f4;">
                <?php echo to_currency($proposal_total_summary->tax, $proposal_total_summary->currency_symbol); ?>
            </td>
        </tr>
    <?php } ?>
    <?php if ($proposal_total_summary->tax2) { ?>
        <tr>
            <td colspan="2" style="text-align: right;"><?php echo $proposal_total_summary->tax_name2; ?></td>
            <td colspan="3" style="text-align: right; width: 15%; border: 1px solid #fff; background-color: #f4f4f4;">
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
        <?php if($proposal_total_summary->gp_apart || $proposal_total_summary->qa_apart) : ?>
            <td style="text-align: right;"><?php echo app_lang("total"); ?></td>
            <td colspan="3" style="text-align: center; width: 20%; background-color: <?php echo $color; ?>; color: #fff;">
                <?php echo $proposal_total_summary->proposal_total_sum_quantity; ?>
            </td>
            <td style="text-align: right; width: 15%; background-color: <?php echo $color; ?>; color: #fff;">
                <?php echo to_currency($proposal_total_summary->proposal_total, $proposal_total_summary->currency_symbol); ?>
            </td>
        <?php else: ?>
            <td colspan="2" style="text-align: right;"><?php echo app_lang("total"); ?></td>
            <td colspan="3" style="text-align: right; width: 15%; background-color: <?php echo $color; ?>; color: #fff;">
                <?php echo to_currency($proposal_total_summary->proposal_total, $proposal_total_summary->currency_symbol); ?>
            </td>
        <?php endif; ?>
    </tr>
</table>