<!--table id="proposal-item-table" class="table display dataTable text-right strong table-responsive"-->
<table width="100%">
    <!-- <tr>
        <td></td>
        <td></td>
        <td></td>
        <td class="w10p text-center"><?php //echo app_lang("sub_total"); ?></td>
        <td class="w10p text-center"><?php //echo $proposal_total_summary->proposal_total_quantity; ?></td>
        <td class="w5p text-right"><?php //echo ($proposal_total_summary->proposal_total_quantity_gp ?? 0); ?></td>
        <td class="w5p text-right"><?php //echo ($proposal_total_summary->proposal_total_quantity_add ?? 0); ?></td>
        <td class="w5p text-center"><?php //echo $proposal_total_summary->proposal_total_sum_quantity; ?></td>
        <td class="w5p text-center"><?php //echo to_currency($proposal_total_summary->proposal_subtotal, $proposal_total_summary->currency_symbol); ?></td>
        <td class="w15p"></td>
    </tr> -->

<?php
    $discount_row = "<tr>
                        <td colspan='4'></td>
                        <td style='padding-top:13px;'>" . app_lang("discount") . "</td>
                        <td class='text-right' style='padding:1rem 2.2rem !important;'>" . to_currency($proposal_total_summary->discount_total, $proposal_total_summary->currency_symbol) . "</td>
                        <td class='text-center option w100'>" . modal_anchor(get_uri("proposals/discount_modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "data-post-proposal_id" => $proposal_id, "title" => app_lang('edit_discount'))) . "<span class='p20'>&nbsp;&nbsp;&nbsp;</span></td>
                    </tr>";

    $total_after_discount_row = "<tr>
                                    <td colspan='4'></td>
                                    <td>" . app_lang("total_after_discount") . "</td>
                                    <td class='text-right' style='width:80px;padding:1rem 2.2rem !important;'>" . to_currency($proposal_total_summary->proposal_subtotal - $proposal_total_summary->discount_total, $proposal_total_summary->currency_symbol) . "</td>
                                    <td></td>
                                </tr>";

    if ($proposal_total_summary->proposal_subtotal && (!$proposal_total_summary->discount_total || ($proposal_total_summary->discount_total !== 0 && $proposal_total_summary->discount_type == "before_tax"))) {
        //when there is discount and type is before tax or no discount
        echo $discount_row;

        if ($proposal_total_summary->discount_total !== 0) {
            echo $total_after_discount_row;
        }
    }
    ?>

    <?php if ($proposal_total_summary->tax) { ?>
        <tr>
            <td colspan='4'></td>
            <td><?php echo $proposal_total_summary->tax_name; ?></td>
            <td class="text-center"><?php echo to_currency($proposal_total_summary->tax, $proposal_total_summary->currency_symbol); ?></td>
            <td></td>
        </tr>
    <?php } ?>
    <?php if ($proposal_total_summary->tax2) { ?>
        <tr>
            <td colspan='4'></td>
            <td><?php echo $proposal_total_summary->tax_name2; ?></td>
            <td class="text-center"><?php echo to_currency($proposal_total_summary->tax2, $proposal_total_summary->currency_symbol); ?></td>
            <td></td>
        </tr>
    <?php } ?>

    <?php
    if ($proposal_total_summary->discount_total && $proposal_total_summary->discount_type == "after_tax") {
        //when there is discount and type is after tax
        echo $discount_row;
    }
    ?>

    <tr>
        <td colspan='4'></td>
        <td><?php echo app_lang("total"); ?></td>
        <td class="text-right" style="padding:1rem 2.2rem !important"><?php echo to_currency($proposal_total_summary->proposal_total, $proposal_total_summary->currency_symbol); ?></td>
        <td></td>
    </tr>
</table>