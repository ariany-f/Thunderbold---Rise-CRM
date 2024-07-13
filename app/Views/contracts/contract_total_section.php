<table id="contract-item-table" class="table display dataTable text-right strong table-responsive">     
    <tr>
        <td><?php echo app_lang("sub_total"); ?></td>
        <td style="width: 120px;"><?php echo to_currency($contract_total_summary->contract_subtotal, $contract_total_summary->currency_symbol); ?></td>
        <td style="width: 100px;"> </td>
    </tr>

    <?php
    $discount_row = "<tr>
                        <td style='padding-top:13px;'>" . app_lang("discount") . "</td>
                        <td style='padding-top:13px;'>" . to_currency($contract_total_summary->discount_total, $contract_total_summary->currency_symbol) . "</td>
                        <td class='text-center option w100'>" . modal_anchor(get_uri("contracts/discount_modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "data-post-contract_id" => $contract_id, "title" => app_lang('edit_discount'))) . "<span class='p20'>&nbsp;&nbsp;&nbsp;</span></td>
                    </tr>";

    $total_after_discount_row = "<tr>
                                    <td>" . app_lang("total_after_discount") . "</td>
                                    <td style='width:120px;'>" . to_currency($contract_total_summary->contract_subtotal - $contract_total_summary->discount_total, $contract_total_summary->currency_symbol) . "</td>
                                    <td></td>
                                </tr>";

    if ($contract_total_summary->contract_subtotal && (!$contract_total_summary->discount_total || ($contract_total_summary->discount_total !== 0 && $contract_total_summary->discount_type == "before_tax"))) {
        //when there is discount and type is before tax or no discount
        echo $discount_row;

        if ($contract_total_summary->discount_total !== 0) {
            echo $total_after_discount_row;
        }
    }
    ?>

    <?php if ($contract_total_summary->tax) { ?>
        <tr>
            <td><?php echo $contract_total_summary->tax_name; ?></td>
            <td><?php echo to_currency($contract_total_summary->tax, $contract_total_summary->currency_symbol); ?></td>
            <td></td>
        </tr>
    <?php } ?>
    <?php if ($contract_total_summary->tax2) { ?>
        <tr>
            <td><?php echo $contract_total_summary->tax_name2; ?></td>
            <td><?php echo to_currency($contract_total_summary->tax2, $contract_total_summary->currency_symbol); ?></td>
            <td></td>
        </tr>
    <?php } ?>

    <?php
    if ($contract_total_summary->discount_total && $contract_total_summary->discount_type == "after_tax") {
        //when there is discount and type is after tax
        echo $discount_row;
    }
    ?>

    <tr>
        <td><?php echo app_lang("total"); ?></td>
        <td><?php echo to_currency($contract_total_summary->contract_total, $contract_total_summary->currency_symbol); ?></td>
        <td></td>
    </tr>
</table>