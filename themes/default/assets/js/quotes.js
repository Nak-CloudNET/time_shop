$(document).ready(function () {

// Order level shipping and discoutn localStorage
    if (qudiscount = localStorage.getItem('qudiscount')) {
        $('#qudiscount').val(qudiscount);
    }
    $('#qutax2').change(function (e) {
        localStorage.setItem('qutax2', $(this).val());
        $('#qutax2').val($(this).val());
    });
    if (qutax2 = localStorage.getItem('qutax2')) {
        $('#qutax2').select2("val", qutax2);
    }
    $('#qustatus').change(function (e) {
        localStorage.setItem('qustatus', $(this).val());
    });
    if (qustatus = localStorage.getItem('qustatus')) {
        $('#qustatus').select2("val", qustatus);
    }
    var old_shipping;
    $('#qushipping').focus(function () {
        old_shipping = $(this).val();
    }).change(function () {
        if (!is_numeric($(this).val())) {
            $(this).val(old_shipping);
            bootbox.alert(lang.unexpected_value);
            return;
        } else {
            shipping = $(this).val() ? parseFloat($(this).val()) : '0';
        }
        localStorage.setItem('qushipping', shipping);
        var gtotal = ((total + invoice_tax) - order_discount) + shipping;
        $('#gtotal').text(formatMoney(gtotal));
        $('#tship').text(formatMoney(shipping));
    });
    if (qushipping = localStorage.getItem('qushipping')) {
        shipping = parseFloat(qushipping);
        $('#qushipping').val(shipping);
    } else {
        shipping = 0;
    }

	$(document).on('keyup', '#amount', function () {
		var us_paid = $('#amount').val()-0;
		var deposit_amount = parseFloat($(".deposit_total_amount").text());
		var deposit_balance = parseFloat($(".deposit_total_balance").text());
		deposit_balance = (deposit_amount - us_paid);
		$(".deposit_total_balance").text(deposit_balance);
	});

	$(document).on('change', '#customer1', function(){
		checkDeposit();
		$('#amount_1').trigger('change');
	});

	$(document).on('load', function(){
		$(".paid_by").trigger('change');
	});

	function checkDeposit() {
		var customer_id = $("#customer1").val();
		if (customer_id != '') {
			$.ajax({
				type: "get", async: false,
				url: site.base_url + "sales/validate_deposit/" + customer_id,
				dataType: "json",
				success: function (data) {
					if (data === false) {
						$('#deposit_no_1').parent('.form-group').addClass('has-error');
						bootbox.alert(lang('invalid_customer'));
					} else if (data.id !== null && data.id !== customer_id) {
						$('#deposit_no_1').parent('.form-group').addClass('has-error');
						bootbox.alert(lang('this_customer_has_no_deposit'));
					} else {
						var amount = $("#amount").val();
						var deposit_amount =  (data.deposit_amount==null?0: data.deposit_amount);
						var deposit_balance = (data.deposit_amount - amount);
						$('#dp_details').html('<small>Customer Name: ' + data.name + '<br>Amount: <span class="deposit_total_amount">' + deposit_amount + '</span> - Balance: <span class="deposit_total_balance">' + deposit_balance + '</span></small>');
						$('#deposit_no').parent('.form-group').removeClass('has-error');
						//calculateTotals();
						//$('#amount_1').val(data.deposit_amount - amount).focus();
					}
				}
			});
		}
	}

	
// If there is any item in localStorage
    if (localStorage.getItem('quitems')) {
        loadItems();
    }

    // clear localStorage and reload
    $('#reset').click(function (e) {
            bootbox.confirm(lang.r_u_sure, function (result) {
                if (result) {
                    if (localStorage.getItem('quitems')) {
                        localStorage.removeItem('quitems');
                    }
                    if (localStorage.getItem('qudiscount')) {
                        localStorage.removeItem('qudiscount');
                    }
                    if (localStorage.getItem('qutax2')) {
                        localStorage.removeItem('qutax2');
                    }
                    if (localStorage.getItem('qushipping')) {
                        localStorage.removeItem('qushipping');
                    }
                    if (localStorage.getItem('quref')) {
                        localStorage.removeItem('quref');
                    }
                    if (localStorage.getItem('quwarehouse')) {
                        localStorage.removeItem('quwarehouse');
                    }
                    if (localStorage.getItem('qunote')) {
                        localStorage.removeItem('qunote');
                    }
                    if (localStorage.getItem('quinnote')) {
                        localStorage.removeItem('quinnote');
                    }
                    if (localStorage.getItem('qucustomer')) {
                        localStorage.removeItem('qucustomer');
                    }
                    if (localStorage.getItem('qucurrency')) {
                        localStorage.removeItem('qucurrency');
                    }
                    if (localStorage.getItem('qudate')) {
                        localStorage.removeItem('qudate');
                    }
                    if (localStorage.getItem('qustatus')) {
                        localStorage.removeItem('qustatus');
                    }
                    if (localStorage.getItem('qubiller')) {
                        localStorage.removeItem('qubiller');
                    }

                    $('#modal-loading').show();
                    location.reload();
                }
            });
    });

// save and load the fields in and/or from localStorage

    $('#quref').change(function (e) {
        localStorage.setItem('quref', $(this).val());
    });
    if (quref = localStorage.getItem('quref')) {
        $('#quref').val(quref);
    }
    $('#quwarehouse').change(function (e) {
        localStorage.setItem('quwarehouse', $(this).val());
    });
    if (quwarehouse = localStorage.getItem('quwarehouse')) {
        $('#quwarehouse').select2("val", quwarehouse);
    }

    $('#qunote').redactor('destroy');
    $('#qunote').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('qunote', v);
        }
    });
    if (qunote = localStorage.getItem('qunote')) {
        $('#qunote').redactor('set', qunote);
    }
    var $customer = $('#qucustomer');
    $customer.change(function (e) {
        localStorage.setItem('qucustomer', $(this).val());
    });
    if (qucustomer = localStorage.getItem('qucustomer')) {
        $customer.val(qucustomer).select2({
            minimumInputLength: 1,
            data: [],
            initSelection: function (element, callback) {
                $.ajax({
                    type: "get", async: false,
                    url: site.base_url+"customers/getCustomer/" + $(element).val(),
                    dataType: "json",
                    success: function (data) {
                        callback(data[0]);
                    }
                });
            },
            ajax: {
                url: site.base_url + "customers/suggestions",
                dataType: 'json',
                quietMillis: 15,
                data: function (term, page) {
                    return {
                        term: term,
                        limit: 10
                    };
                },
                results: function (data, page) {
                    if (data.results != null) {
                        return {results: data.results};
                    } else {
                        return {results: [{id: '', text: 'No Match Found'}]};
                    }
                }
            }
        });
    } else {
        nsCustomer();
    }


// prevent default action upon enter
    $('body').bind('keypress', function (e) {
        if (e.keyCode == 13) {
            e.preventDefault();
            return false;
        }
    });

// Order tax calculation
if (site.settings.tax2 != 0) {
    $('#qutax2').change(function () {
        localStorage.setItem('qutax2', $(this).val());
        loadItems();
        return;
    });
}

// Order discount calculation
var old_qudiscount;
$('#qudiscount').focus(function () {
    old_qudiscount = $(this).val();
}).change(function () {
    var new_discount = $(this).val() ? $(this).val() : '0';
    if (is_valid_discount(new_discount)) {
        localStorage.removeItem('qudiscount');
        localStorage.setItem('qudiscount', new_discount);
        loadItems();
        return;
    } else {
        $(this).val(old_qudiscount);
        bootbox.alert(lang.unexpected_value);
        return;
    }

});

/* ----------------------
 * Delete Row Method
 * ---------------------- */
$(document).on('click', '.qudel', function () {
    var row = $(this).closest('tr');
    var item_id = row.attr('data-item-id');
	//alert(item_id);
    delete quitems[item_id];
    row.remove();
	//alert(quitems.hasOwnProperty(item_id));
    if(quitems.hasOwnProperty(item_id)) { } else {
        localStorage.setItem('quitems', JSON.stringify(quitems));
        loadItems();
        return;
    }
});

    /* -----------------------
     * Edit Row Modal Hanlder
     ----------------------- */
     $(document).on('click', '.edit', function () {
        var row = $(this).closest('tr');
        var row_id = row.attr('id');
        item_id = row.attr('data-item-id');
        item = quitems[item_id];
        var qty = row.children().children('.rquantity').val(),
        product_option = row.children().children('.roption').val(),
        unit_price = formatDecimal(row.children().children('.realuprice').val()),
        discount = row.children().children('.rdiscount').val();
        var net_price = unit_price;
        $('#prModalLabel').text(item.row.name + ' (' + item.row.code + ')');
        if (site.settings.tax1) {
            $('#ptax').select2('val', item.row.tax_rate);
            $('#old_tax').val(item.row.tax_rate);
            var item_discount = 0, ds = discount ? discount : '0';
            if (ds.indexOf("%") !== -1) {
                var pds = ds.split("%");
                if (!isNaN(pds[0])) {
                    item_discount = parseFloat(((unit_price) * parseFloat(pds[0])) / 100);
                } else {
                    item_discount = parseFloat(ds);
                }
            } else {
                item_discount = parseFloat(ds);
            }

            var pr_tax = item.row.tax_rate, pr_tax_val = 0;
            if (pr_tax !== null && pr_tax != 0) {
                $.each(tax_rates, function () {
                    if(this.id == pr_tax){
                        if (this.type == 1) {

                            if (quitems[item_id].row.tax_method == 0) {
                                pbt = unit_price / (1 + (parseFloat(this.rate) / 100))
                                pr_tax_val = formatDecimal((unit_price) - pbt);
                                //pr_tax_val = formatDecimal(((unit_price) * parseFloat(this.rate)) / 100);
                                pr_tax_rate = formatDecimal(this.rate) + '%';
                               
                            } else {
                                pbt = unit_price / (1 + (parseFloat(this.rate) / 100))
                                pr_tax_val = formatDecimal((unit_price) - pbt);
                                //pr_tax_val = formatDecimal(((unit_price) * parseFloat(this.rate)) / 100);
                                pr_tax_rate = formatDecimal(this.rate) + '%';
                            }

                        } else if (this.type == 2) {

                            pr_tax_val = parseFloat(this.rate);
                            pr_tax_rate = this.rate;

                        }
                    }
                });
            }
        }
        if (site.settings.product_serial !== 0) {
            $('#pserial').val(row.children().children('.rserial').val());
        }
        var opt = '<p style="margin: 12px 0 0 0;">n/a</p>';
        if(item.options !== false) {
            var o = 1;
            opt = $("<select id=\"poption\" name=\"poption\" class=\"form-control select\" />");
            $.each(item.options, function () {
                if(o == 1) {
                    if(product_option == '') { product_variant = this.id; } else { product_variant = product_option; }
                }
                $("<option />", {value: this.id, text: this.name}).appendTo(opt);
                o++;
            });
        }

        $('#poptions-div').html(opt);
        $('select.select').select2({minimumResultsForSearch: 6});
        $('#pquantity').val(qty);
        $('#old_qty').val(qty);
        $('#pprice').val(unit_price);
        $('#punit_price').val(formatDecimal(parseFloat(unit_price)+parseFloat(pr_tax_val)));
        $('#poption').select2('val', item.row.option);
        $('#old_price').val(unit_price);
        $('#row_id').val(row_id);
        $('#item_id').val(item_id);
        $('#pserial').val(row.children().children('.rserial').val());
        $('#pdiscount').val(discount);
        $('#net_price').text(formatMoney(net_price - pr_tax_val));
        $('#pro_tax').text(formatMoney(pr_tax_val));
        $('#prModal').appendTo("body").modal('show');

    });

    $('#prModal').on('shown.bs.modal', function (e) {
        if($('#poption').select2('val') != '') {
            $('#poption').select2('val', product_variant);
            product_variant = 0;
        }
    });

    $(document).on('change', '#pprice, #ptax, #pdiscount', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var unit_price = parseFloat($('#pprice').val());
        var item = quitems[item_id];
        var ds = $('#pdiscount').val() ? $('#pdiscount').val() : '0';
        if (ds.indexOf("%") !== -1) {
            var pds = ds.split("%");
            if (!isNaN(pds[0])) {
                item_discount = parseFloat(((unit_price) * parseFloat(pds[0])) / 100);
            } else {
                item_discount = parseFloat(ds);
            }
        } else {
            item_discount = parseFloat(ds);
        }
        //unit_price -= item_discount;
        var pr_tax = $('#ptax').val(), item_tax_method = item.row.tax_method;
        var pr_tax_val = 0, pr_tax_rate = 0;
        if (pr_tax !== null && pr_tax != 0) {
            $.each(tax_rates, function () {
                if(this.id == pr_tax){
                    if (this.type == 1) {

                        if (item_tax_method == 0) {
                            pbt = unit_price / (1 + (parseFloat(this.rate) / 100))
 			        		pr_tax_val = formatDecimal((unit_price) - pbt);
                            pr_tax_rate = formatDecimal(this.rate) + '%';
                        } else {
                            pbt = unit_price / (1 + (parseFloat(this.rate) / 100))
                            pr_tax_val = formatDecimal((unit_price) - pbt);
                            pr_tax_rate = formatDecimal(this.rate) + '%';
                        }

                    } else if (this.type == 2) {

                        pr_tax_val = parseFloat(this.rate);
                        pr_tax_rate = this.rate;

                    }
                }
            });
        }

        $('#net_price').text(formatMoney(unit_price - pr_tax_val));
        $('#pro_tax').text(formatMoney(pr_tax_val));
    });

    /* -----------------------
     * Edit Row Method
     ----------------------- */
     $(document).on('click', '#editItem', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id'), new_pr_tax = $('#ptax').val(), new_pr_tax_rate = {};
        if (new_pr_tax) {
            $.each(tax_rates, function () {
                if (this.id == new_pr_tax) {
                    new_pr_tax_rate = this;
                }
            });
        } else {
            new_pr_tax_rate = false;
        }
        var price = parseFloat($('#pprice').val());
        if (site.settings.product_discount == 1 && $('#pdiscount').val()) {
            if(!is_valid_discount($('#pdiscount').val()) || $('#pdiscount').val() > price) {
                bootbox.alert(lang.unexpected_value);
                return false;
            }
        }
        quitems[item_id].row.qty = parseFloat($('#pquantity').val()),
        quitems[item_id].row.real_unit_price = price,
        quitems[item_id].row.tax_rate = new_pr_tax,
        quitems[item_id].tax_rate = new_pr_tax_rate,
        quitems[item_id].row.discount = $('#pdiscount').val() ? $('#pdiscount').val() : '',
        quitems[item_id].row.option = $('#poption').val() ? $('#poption').val() : '',
        quitems[item_id].row.serial = $('#pserial').val();
        localStorage.setItem('quitems', JSON.stringify(quitems));
        $('#prModal').modal('hide');

        loadItems();
        return;
    });

    /* -----------------------
     * Product option change
     ----------------------- */
     $(document).on('change', '#poption', function () {
        var row = $('#' + $('#row_id').val()), opt = $(this).val();
        var item_id = row.attr('data-item-id');
        var item = quitems[item_id];
        if(item.options !== false) {
            $.each(item.options, function () {
                if(this.id == opt && this.price != 0 && this.price != '' && this.price != null) {
                    $('#pprice').val(this.price);
                }
            });
        }
    });

    /* ------------------------------
     * Show manual item addition modal
     ------------------------------- */
     $(document).on('click', '#addManually', function (e) {
        if (count == 1) {
            quitems = {};
            if ($('#qucustomer').val()) {
                $('#qucustomer').select2("readonly", true);
                $('#quwarehouse').select2("readonly", true);
            } else {
                bootbox.alert(lang.select_above);
                item = null;
                return false;
            }
        }
        $('#mnet_price').text('0.00');
        $('#mpro_tax').text('0.00');
        $('#mModal').appendTo("body").modal('show');
        return false;
    });

     $(document).on('click', '#addItemManually', function (e) {
        var mid = (new Date).getTime(),
        mcode = $('#mcode').val(),
        mname = $('#mname').val(),
        mtax = parseInt($('#mtax').val()),
        mqty = parseFloat($('#mquantity').val()),
        mdiscount = $('#mdiscount').val() ? $('#mdiscount').val() : '0',
        unit_price = parseFloat($('#mprice').val()),
        mtax_rate = {};
        $.each(tax_rates, function () {
            if (this.id == mtax) {
                mtax_rate = this;
            }
        });

        quitems[mid] = {"id": mid, "item_id": mid, "label": mname + ' (' + mcode + ')', "row": {"id": mid, "code": mcode, "name": mname, "quantity": mqty, "price": unit_price, "unit_price": unit_price, "real_unit_price": unit_price, "tax_rate": mtax, "tax_method": 0, "qty": mqty, "type": "manual", "discount": mdiscount, "serial": "", "option":""}, "tax_rate": mtax_rate, "options":false};
        localStorage.setItem('quitems', JSON.stringify(quitems));
        loadItems();
        $('#mModal').modal('hide');
        $('#mcode').val('');
        $('#mname').val('');
        $('#mtax').val('');
        $('#mquantity').val('');
        $('#mdiscount').val('');
        $('#mprice').val('');
        return false;
    });

    $(document).on('change', '#mprice, #mtax, #mdiscount', function () {
        var unit_price = parseFloat($('#mprice').val());
        var ds = $('#mdiscount').val() ? $('#mdiscount').val() : '0';
        if (ds.indexOf("%") !== -1) {
            var pds = ds.split("%");
            if (!isNaN(pds[0])) {
                item_discount = parseFloat(((unit_price) * parseFloat(pds[0])) / 100);
            } else {
                item_discount = parseFloat(ds);
            }
        } else {
            item_discount = parseFloat(ds);
        }
        unit_price -= item_discount;
        var pr_tax = $('#mtax').val(), item_tax_method = 0;
        var pr_tax_val = 0, pr_tax_rate = 0;
        if (pr_tax !== null && pr_tax != 0) {
            $.each(tax_rates, function () {
                if(this.id == pr_tax){
                    if (this.type == 1) {

                        if (item_tax_method == 0) {
                            pr_tax_val = formatDecimal(((unit_price) * parseFloat(this.rate)) / (100 + parseFloat(this.rate)));
                            pr_tax_rate = formatDecimal(this.rate) + '%';
                            unit_price -= pr_tax_val;
                        } else {
                            pr_tax_val = formatDecimal(((unit_price) * parseFloat(this.rate)) / 100);
                            pr_tax_rate = formatDecimal(this.rate) + '%';
                        }

                    } else if (this.type == 2) {

                        pr_tax_val = parseFloat(this.rate);
                        pr_tax_rate = this.rate;

                    }
                }
            });
        }

        $('#mnet_price').text(formatMoney(unit_price));
        $('#mpro_tax').text(formatMoney(pr_tax_val));
    });

    /* --------------------------
     * Edit Row Quantity Method
     -------------------------- */
     var old_row_qty;
     $(document).on("focus", '.rquantity', function () {
        old_row_qty = $(this).val();
    }).on("change", '.rquantity', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val())) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var new_qty = parseFloat($(this).val()),
        item_id = row.attr('data-item-id');
        quitems[item_id].row.qty = new_qty;
        localStorage.setItem('quitems', JSON.stringify(quitems));
        loadItems();
    });

    /* --------------------------
     * Edit Row Price Method
     -------------------------- */
    var old_price;
    $(document).on("focus", '.rprice', function () {
        old_price = $(this).val();
    }).on("change", '.rprice', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val())) {
            $(this).val(old_price);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var new_price = parseFloat($(this).val()),
                item_id = row.attr('data-item-id');
        quitems[item_id].row.price = new_price;
        localStorage.setItem('quitems', JSON.stringify(quitems));
        loadItems();
    });

    $(document).on("click", '#removeReadonly', function () {
       $('#qucustomer').select2('readonly', false);
       //$('#quwarehouse').select2('readonly', false);
       return false;
    });


});
/* -----------------------
 * Misc Actions
 ----------------------- */

// hellper function for customer if no localStorage value
function nsCustomer() {
    $('#qucustomer').select2({
        minimumInputLength: 1,
        ajax: {
            url: site.base_url + "customers/suggestions",
            dataType: 'json',
            quietMillis: 15,
            data: function (term, page) {
                return {
                    term: term,
                    limit: 10
                };
            },
            results: function (data, page) {
                if (data.results != null) {
                    return {results: data.results};
                } else {
                    return {results: [{id: '', text: 'No Match Found'}]};
                }
            }
        }
    });
}
//localStorage.clear();
function loadItems() {

    if (localStorage.getItem('quitems')) {
        total = 0;
		f_total = 0;
        count = 1;
        an = 1;
        product_tax = 0;
        invoice_tax = 0;
        product_discount = 0;
        order_discount = 0;
        total_discount = 0;

        $("#quTable tbody").empty();
        quitems = JSON.parse(localStorage.getItem('quitems'));
        $('#add_sale, #edit_sale').attr('disabled', false);
        $.each(quitems, function () {
			
            var item = this;
            var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
            quitems[item_id] = item;

            var product_id = item.row.id,
                item_type = item.row.type,
                combo_items = item.combo_items,
                item_price = item.row.price,
                item_price = item.row.price,
                item_qty = item.row.qty,
                item_aqty = item.row.quantity,
                item_tax_method = item.row.tax_method,
                item_ds = item.row.discount,
                item_discount = 0,
                item_option = item.row.option,
                item_code = item.row.code,
                item_serial = item.row.serial,
                item_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;");
            var unit_price = item.row.real_unit_price;
            var ds = item_ds ? item_ds : '0';
			alert(item_qty);
            if (ds.indexOf("%") !== -1) {
                var pds = ds.split("%");
                if (!isNaN(pds[0])) {
                    item_discount = formatDecimal(parseFloat(((unit_price) * parseFloat(pds[0])) / 100));
                } else {
                    item_discount = formatDecimal(ds);
                }
            } else {
                 item_discount = parseFloat(ds);
            }
            product_discount += parseFloat(item_discount * item_qty);
            //unit_price = formatDecimal(unit_price-item_discount);
			final_price = formatDecimal(unit_price-item_discount)
            unit_price = formatDecimal(unit_price);

            var pr_tax = item.tax_rate;
            var pr_tax_val = 0, pr_tax_rate = 0;
            if (site.settings.tax1 == 1) {
                if (pr_tax !== false) {
                    if (pr_tax.type == 1) {

                        if (item_tax_method == '0') {
							pbt = (unit_price) / (1 + (parseFloat(pr_tax.rate) / 100));
                            pr_tax_val = formatDecimal(unit_price - pbt);
                            pr_tax_rate = formatDecimal(pr_tax.rate) + '%';
                        } else {
                            pbt = formatDecimal((unit_price) / (1 + (parseFloat(pr_tax.rate) / 100)));
                            pr_tax_val = formatDecimal(unit_price - pbt);
                            pr_tax_rate = formatDecimal(pr_tax.rate) + '%';
                        }

                    } else if (pr_tax.type == 2) {

                        pr_tax_val = parseFloat(pr_tax.rate);
                        pr_tax_rate = pr_tax.rate;

                    }
                    product_tax += pr_tax_val * item_qty;
                }
            }
			item_price = item_tax_method == 0 ? formatDecimal(unit_price) : formatDecimal(unit_price);
            unit_price = formatDecimal(unit_price-item_discount);

            var sel_opt = '';
            $.each(item.options, function () {
                if(this.id == item_option) {
                    sel_opt = this.name;
                }
            });
            var row_no = (new Date).getTime();
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
			
            //Code and Name
			tr_html = '<td><input name="product_id[]" type="hidden" class="rid" value="' + product_id + '"><input name="product_type[]" type="hidden" class="rtype" value="' + item_type + '"><input name="product_code[]" type="hidden" class="rcode" value="' + item_code + '"><input name="product_name[]" type="hidden" class="rname" value="' + item_name + '"><input name="product_option[]" type="hidden" class="roption" value="' + item_option + '"><span class="sname" id="name_' + row_no + '">' + item_name + ' (' + item_code + ')'+(sel_opt != '' ? ' ('+sel_opt+')' : '')+'</span> <i class="pull-right fa fa-edit tip pointer edit" id="' + row_no + '" data-item="' + item_id + '" title="Edit" style="cursor:pointer;"></i></td>';
            
			//PBT
			tr_html += '<td class="text-right">'+formatMoney(item_price - pr_tax_val)+'</td>';
			
			//Tax
			if (site.settings.tax1 == 1) {
                tr_html += '<td class="text-right"><input class="form-control input-sm text-right rproduct_tax" name="product_tax[]" type="hidden" id="product_tax_' + row_no + '" value="' + pr_tax.id + '"><span class="text-right sproduct_tax" id="sproduct_tax_' + row_no + '">' + (pr_tax_rate ? '(' + pr_tax_rate + ')' : '') + ' ' + /*formatMoney(pr_tax_val * item_qty)*/ formatMoney(pr_tax_val) + '</span></td>';
            }
			
			//PAT
			tr_html += '<td class="text-right"><input class="form-control input-sm text-right rprice" name="net_price[]" type="hidden" id="price_' + row_no + '" value="' + formatDecimal(item_price) + '"><input class="ruprice" name="unit_price[]" type="hidden" value="' + unit_price + '"><input class="realuprice" name="real_unit_price[]" type="hidden" value="' + item.row.real_unit_price + '"><span class="text-right sprice" id="sprice_' + row_no + '">' + formatMoney(item_price) + '</span></td>';
			
			//Quantity
            tr_html += '<td><input class="form-control text-center rquantity" name="quantity[]" type="text" readonly value="' + formatDecimal(item_qty) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();"></td>';
			
			//Subtotal
			tr_html += '<td class="text-right"><span class="text-right ssubtotal" id="subtotal_' + row_no + '">' + formatMoney(((parseFloat(item_price)) * parseFloat(item_qty))) + '</span></td>';
			
			//Discount
            if (site.settings.product_discount == 1) {
                tr_html += '<td class="text-right"><input class="form-control input-sm rdiscount" name="product_discount[]" type="hidden" id="discount_' + row_no + '" value="' + item_ds + '"><span class="text-right sdiscount text-danger" id="sdiscount_' + row_no + '">' + formatMoney(0 - (item_discount * item_qty)) + '</span></td>';
            }

			//Final Price
            tr_html += '<td class="text-right"><span class="text-right finalprice" id="finalprice_' + row_no + '">' + formatMoney(((parseFloat(final_price)) * parseFloat(item_qty))) + '</span></td>';

			//Remove
            tr_html += '<td class="text-center"><i class="fa fa-times tip pointer qudel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
            newTr.html(tr_html);
            newTr.prependTo("#quTable");
            //total += formatDecimal(((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty)));
			total += formatDecimal(((parseFloat(item_price)) * parseFloat(item_qty)));
			
			f_total += formatDecimal(((parseFloat(final_price)) * parseFloat(item_qty)));
			
            count += parseFloat(item_qty);
            an++;
            if (item_type == 'standard' && item.options !== false) {
                $.each(item.options, function () {
                    if(this.id == item_option && item_qty > this.quantity) {
                        $('#row_' + row_no).addClass('danger');
                        if(site.settings.overselling != 1) { $('#add_sale, #edit_sale').attr('disabled', true); }
                    }
                });
            } else if(item_type == 'standard' && item_qty > item_aqty) {
                $('#row_' + row_no).addClass('danger');
            } else if (item_type == 'combo') {
                if(combo_items === false) {
                    $('#row_' + row_no).addClass('danger');
                } else {
                    $.each(combo_items, function() {
                       if(parseFloat(this.quantity) < (parseFloat(this.qty)*item_qty) && this.type == 'standard') {
                           $('#row_' + row_no).addClass('danger');
                       }
                   });
                }
            }

        });

        var col = 4;
        var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="'+col+'">Total</th><th class="text-center">' + formatNumber(parseFloat(count) - 1) + '</th>';
        /*if (site.settings.tax1 == 1) {
            tfoot += '<th class="text-right">'+formatMoney(product_tax)+'</th>';
        }*/
        tfoot += '<th class="text-right">'+formatMoney(total)+'</th>';
        if (site.settings.product_discount == 1) {
            tfoot += '<th class="text-right">'+formatMoney(product_discount)+'</th>';
        }
        tfoot += '<th class="text-right">'+formatMoney(f_total)+'</th><th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
        $('#quTable tfoot').html(tfoot);

        // Order level discount calculations
        if (qudiscount = localStorage.getItem('qudiscount')) {
            var ds = qudiscount;
            if (ds.indexOf("%") !== -1) {
                var pds = ds.split("%");
                if (!isNaN(pds[0])) {
                    order_discount = parseFloat(((total) * parseFloat(pds[0])) / 100);
                } else {
                    order_discount = parseFloat(ds);
                }
            } else {
                order_discount = parseFloat(ds);
            }
        }

        // Order level tax calculations
        if (site.settings.tax2 != 0) {
            if (qutax2 = localStorage.getItem('qutax2')) {
                $.each(tax_rates, function () {
                    if (this.id == qutax2) {
                        if (this.type == 2) {
                            invoice_tax = parseFloat(this.rate);
                        }
                        if (this.type == 1) {
                            invoice_tax = parseFloat(((total - order_discount) * this.rate) / 100);
                        }
                    }
                });
            }
        }
        total_discount = parseFloat(order_discount + product_discount);
        // Totals calculations after item addition
        var gtotal = parseFloat(((f_total) - order_discount) + shipping);
        $('#total').text(formatMoney(total));
        $('#titems').text((an - 1) + ' (' + formatNumber(parseFloat(count) - 1) + ')');
        $('#total_items').val((parseFloat(count) - 1));
        $('#tds').text(formatMoney(total_discount));
        if (site.settings.tax2 != 0) {
            $('#ttax2').text(formatMoney(product_tax));
        }
        $('#tship').text(formatMoney(shipping));
        $('#gtotal').text(formatMoney(gtotal));
        if (an > site.settings.bc_fix && site.settings.bc_fix != 0) {
            $("html, body").animate({scrollTop: $('#quTable').offset().top - 150}, 500);
            $(window).scrollTop($(window).scrollTop() + 1);
        }
        //audio_success.play();
    }
}

/* -----------------------------
 * Add Quotation Item Function
 * @param {json} item
 * @returns {Boolean}
 ---------------------------- */
 function add_invoice_item(item) {
	//alert(JSON.stringify(item));
    if (count == 1) {
        quitems = {};
        if ($('#qucustomer').val()) {
            $('#qucustomer').select2("readonly", true);
           //$('#quwarehouse').select2("readonly", true);
        } else {
            bootbox.alert(lang.select_above);
            item = null;
            return;
        }
    }
    if (item == null) {
        return;
    }
    var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
    if (quitems[item_id]) {
        quitems[item_id].row.qty = parseFloat(quitems[item_id].row.qty) + 1;
    } else {
        quitems[item_id] = item;
    }
	//alert(JSON.stringify(quitems));
    localStorage.setItem('quitems', JSON.stringify(quitems));
    loadItems();
    return true;
}

if (typeof (Storage) === "undefined") {
    $(window).bind('beforeunload', function (e) {
        if (count > 1) {
            var message = "You will loss data!";
            return message;
        }
    });
}
