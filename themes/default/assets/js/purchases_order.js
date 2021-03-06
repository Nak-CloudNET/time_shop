$(document).ready(function () {

	// Order level shipping and discoutn localStorage 
	if (podiscount = localStorage.getItem('podiscount')) {
		$('#podiscount').val(podiscount);
	}
	$('#potax2').change(function (e) {
		localStorage.setItem('potax2', $(this).val());
	});
	if (potax2 = localStorage.getItem('potax2')) {
		$('#potax2').select2("val", potax2);
	}
	$('#postatus').change(function (e) {
		localStorage.setItem('postatus', $(this).val());
	});
	if (postatus = localStorage.getItem('postatus')) {
		$('#postatus').select2("val", postatus);
	}
	var old_shipping;
	$('#poshipping').focus(function () {
		old_shipping = $(this).val();
	}).change(function () {
		if (!is_numeric($(this).val())) {
			$(this).val(0);
			//bootbox.alert(lang.unexpected_value);
			return;
		} else {
			shipping = $(this).val() ? parseFloat($(this).val()) : '0';
		}
		localStorage.setItem('poshipping', shipping);
		var gtotal = ((total + invoice_tax) - order_discount) + shipping;
		$('#gtotal').text(formatPurDecimal(gtotal));
		$('#tship').text(formatPurDecimal(shipping));
	});
	if (poshipping = localStorage.getItem('poshipping')) {
		shipping = parseFloat(poshipping);
		$('#poshipping').val(shipping);
	}
	// If there is any item in localStorage
	if (localStorage.getItem('poitems')) {
		loadItems();
	}

    // clear localStorage and reload
    $('#reset').click(function (e) {
        bootbox.confirm(lang.r_u_sure, function (result) {
            if (result) {
                if (localStorage.getItem('poitems')) {
                    localStorage.removeItem('poitems');
                }
                if (localStorage.getItem('podiscount')) {
                    localStorage.removeItem('podiscount');
                }
                if (localStorage.getItem('potax2')) {
                    localStorage.removeItem('potax2');
                }
                if (localStorage.getItem('poshipping')) {
                    localStorage.removeItem('poshipping');
                }
                if (localStorage.getItem('poref')) {
                    localStorage.removeItem('poref');
                }
                if (localStorage.getItem('powarehouse')) {
                    localStorage.removeItem('powarehouse');
                }
                if (localStorage.getItem('ponote')) {
                    localStorage.removeItem('ponote');
                }
                if (localStorage.getItem('posupplier')) {
                    localStorage.removeItem('posupplier');
                }
                if (localStorage.getItem('pocurrency')) {
                    localStorage.removeItem('pocurrency');
                }
                if (localStorage.getItem('poextras')) {
                    localStorage.removeItem('poextras');
                }
                if (localStorage.getItem('podate')) {
                    localStorage.removeItem('podate');
                }
                if (localStorage.getItem('postatus')) {
                    localStorage.removeItem('postatus');
                }

                 $('#modal-loading').show();
                 location.reload();
             }
        });
	});

	// save and load the fields in and/or from localStorage
	var $supplier = $('#posupplier'), $currency = $('#pocurrency');

	$('#poref').change(function (e) {
		localStorage.setItem('poref', $(this).val());
	});
	if (poref = localStorage.getItem('poref')) {
		$('#poref').val(poref);
	}
	$('#powarehouse').change(function (e) {
		localStorage.setItem('powarehouse', $(this).val());
	});
	if (powarehouse = localStorage.getItem('powarehouse')) {
		$('#powarehouse').select2("val", powarehouse);
	}

	$('#ponote').redactor('destroy');
	$('#ponote').redactor({
		buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
		formattingTags: ['p', 'pre', 'h3', 'h4'],
		minHeight: 100,
		changeCallback: function (e) {
			var v = this.get();
			localStorage.setItem('ponote', v);
		}
	});
	if (ponote = localStorage.getItem('ponote')) {
		$('#ponote').redactor('set', ponote);
	}
	$supplier.change(function (e) {
		localStorage.setItem('posupplier', $(this).val());
		$('#supplier_id').val($(this).val());
	});
	if (posupplier = localStorage.getItem('posupplier')) {
		$supplier.val(posupplier).select2({
			minimumInputLength: 1,
			data: [],
			initSelection: function (element, callback) {
				$.ajax({
					type: "get", async: false,
					url: site.base_url+"suppliers/getSupplier/" + $(element).val(),
					dataType: "json",
					success: function (data) {
						callback(data[0]);
					}
				});
			},
			ajax: {
				url: site.base_url + "suppliers/suggestions",
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
		nsSupplier();
	}

	if (localStorage.getItem('poextras')) {
		$('#extras').iCheck('check');
		$('#extras-con').show();
	}
	$('#extras').on('ifChecked', function () {
		localStorage.setItem('poextras', 1);
		$('#extras-con').slideDown();
	});
	$('#extras').on('ifUnchecked', function () {
		localStorage.removeItem("poextras");
		$('#extras-con').slideUp();
	});
	$(document).on('change', '.rexpiry', function () { 
		var item_id = $(this).closest('tr').attr('data-item-id');
		poitems[item_id].row.expiry = $(this).val();
		localStorage.setItem('poitems', JSON.stringify(poitems));
	});
	
	// prevent default action upon enter
	$('body').bind('keypress', function (e) {
		if (e.keyCode == 13) {
			e.preventDefault();
			return false;
		}
	});

	// Order tax calcuation 
	if (site.settings.tax2 != 0) {
		$('#potax2').change(function () {
			localStorage.setItem('potax2', $(this).val());
			loadItems();
			return;
		});
	}

	// Order discount calcuation 
	var old_podiscount;
	$('#podiscount').focus(function () {
		old_podiscount = $(this).val();
	}).change(function () {
		if (is_valid_discount($(this).val())) {
			localStorage.removeItem('podiscount');
			localStorage.setItem('podiscount', $(this).val());
			loadItems();
			return;
		} else {
			$(this).val(old_podiscount);
			bootbox.alert(lang.unexpected_value);
			return;
		}

	});

    /* ---------------------- 
     * Delete Row Method 
     * ---------------------- */

	$(document).on('click', '.podel', function () {
		var row = $(this).closest('tr');
		var item_id = row.attr('data-item-id');
		if (site.settings.product_discount == 1) {
			idiscount = formatPurDecimal($.trim(row.children().children('.rdiscount').text()));
			total_discount -= idiscount;
		}
		if (site.settings.tax1 == 1) {
			var itax = row.children().children('.sproduct_tax').text();
			var iptax = itax.split(') ');
			var iproduct_tax = parseFloat(iptax[1]);
			product_tax -= iproduct_tax;
		}
		var iqty = parseFloat(row.children().children('.rquantity').val());
		var icost = parseFloat(row.children().children('.rcost').val());
		an -= 1;
		total -= (iqty * icost);
		count -= iqty;

		var gtotal = ((total + product_tax + invoice_tax) - total_discount) + shipping;
		$('#total').text(formatPurDecimal(total));
		$('#tds').text(formatPurDecimal(total_discount));
		$('#titems').text(count - 1);
		$('#ttax1').text(formatPurDecimal(product_tax));
		$('#gtotal').text(formatPurDecimal(gtotal));
		if (count == 1) {
        $('#posupplier').select2('readonly', false);
            //$('#pocurrency').select2('readonly', false);
        }
        //console.log(poitems[item_id].row.name + ' is being removed.');
        delete poitems[item_id];
        localStorage.setItem('poitems', JSON.stringify(poitems));
        row.remove();
		loadItems();
	});
	
    /* -----------------------
     * Edit Row Modal Hanlder 
     ----------------------- */
    $(document).on('click', '.edit', function () {
		var row           = $(this).closest('tr');
        var row_id        = row.attr('id');
        var receive 	  = row.find(".received").val(); 
		var pstatus       = $('#postatus').val(); 
        item_id = row.attr('data-item-id');
        item = poitems[item_id];
        var qty  = row.children().children('.rquantity').val(),  
        product_option = row.children().children('.roption').val(),
        unit_cost = formatPurDecimal(row.children().children('.realucost').val()),
        discount = row.children().children('.rdiscount').val(),
        supplier = row.children().children('.rsupplier_id').val();
        var qtyr = row.children().children('.received').val();
		
		
		
		if(qtyr == undefined){
			qtyr = qty;
		}
        var net_cost = unit_cost;
        $('#prModalLabel').text(item.row.name + ' (' + item.row.code + ')');
		var code = item.row.code;

        var results = [];
        $.ajax({
			type: "get",
			dataType: "json",
			async: false,
			url: site.base_url+"purchases/getSupplierProduct/",
			data: { code: code},
			success: function (data) {
                results = data;
			}
		});
        $('#psupplier').select2({
            data : results
        });
        $('#psupplier').select2('val', item.supplier_id);

        if (site.settings.tax1) {
            $('#ptax').select2('val', item.row.tax_rate);
            $('#old_tax').val(item.row.tax_rate);
            var item_discount = 0, ds = discount ? discount : '0';
            if (ds.indexOf("%") !== -1) {
                var pds = ds.split("%");
                if (!isNaN(pds[0])) {
                    item_discount = parseFloat(((unit_cost) * parseFloat(pds[0])) / 100);
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

                            if (poitems[item_id].row.tax_method == 0) {
                                pr_tax_val = formatPurDecimal(((unit_cost) * parseFloat(this.rate)) / (100 + parseFloat(this.rate)));
                                pr_tax_rate = formatPurDecimal(this.rate) + '%';
                                net_cost -= pr_tax_val;
                            } else {
                                pr_tax_val = formatPurDecimal(((unit_cost) * parseFloat(this.rate)) / 100);
                                pr_tax_rate = formatPurDecimal(this.rate) + '%';
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
        if(item.options !== false){
            var o = 1;
            opt = $("<select id=\"poption\" name=\"poption\" class=\"form-control select\" />");
            $.each(item.options, function () {
                if(o == 1){
                    if(product_option == ''){ product_variant = this.id; } else { product_variant = product_option; }
                }
                $("<option />", {value: this.id, text: this.name}).appendTo(opt);
                o++;
            });
        }
		
		if(pstatus == 'ordered' || pstatus == 'pending'){
			input = '<input type="text" class="form-control serial_no">';
			$('#serial').html(input);
			$('.serial_no').attr("disabled","disabled");
		}
		
        $('#qtyreceive').val(receive);
        $('#poptions-div').html(opt);
        $('select.select').select2({minimumResultsForSearch: 6});
        $('#pquantity').val(qty);
        $('#psupplier').val(psupplier);
        $('#old_qty').val(qty);
        $('#pcost').val(unit_cost);
        $('#punit_cost').val(formatPurDecimal(parseFloat(unit_cost)+parseFloat(pr_tax_val)));
        $('#poption').select2('val', item.row.option);
        $('#old_cost').val(unit_cost);
        $('#row_id').val(row_id);
        $('#item_id').val(item_id);
        $('#pexpiry').val(row.children().children('.rexpiry').val());
        $('#pdiscount').val(discount);
        $('#net_cost').text(formatPurDecimal(net_cost));
        $('#pro_tax').text(formatPurDecimal(pr_tax_val));
        $('#prModal').appendTo("body").modal('show');

    });
	
    $('#prModal').on('shown.bs.modal', function (e) {
        if($('#poption').select2('val') != '') {
            $('#poption').select2('val', product_variant);
            product_variant = 0;
        }
    });
	
    $(document).on('change', '#pcost, #ptax', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var unit_cost = parseFloat($('#pcost').val());
        var item = poitems[item_id];
        var ds = $('#pdiscount').val() ? $('#pdiscount').val() : '0';
        if (ds.indexOf("%") !== -1) {
            var pds = ds.split("%");
            if (!isNaN(pds[0])) {
                item_discount = parseFloat(((unit_cost) * parseFloat(pds[0])) / 100);
            } else {
                item_discount = parseFloat(ds);
            }
        } else {
            item_discount = parseFloat(ds);
        }
        //unit_cost -= item_discount;
        var pr_tax = $('#ptax').val(), item_tax_method = item.row.tax_method;
        var pr_tax_val = 0, pr_tax_rate = 0;
        if (pr_tax !== null && pr_tax != 0) {
            $.each(tax_rates, function () {
                if(this.id == pr_tax){
                    if (this.type == 1) {

                        if (item_tax_method == 0) {
                            pr_tax_val = formatPurDecimal(((unit_cost) * parseFloat(this.rate)) / (100 + parseFloat(this.rate)));
                            pr_tax_rate = formatPurDecimal(this.rate) + '%';
                            unit_cost -= pr_tax_val;
                        } else {
                            pr_tax_val = formatPurDecimal(((unit_cost) * parseFloat(this.rate)) / 100);
                            pr_tax_rate = formatPurDecimal(this.rate) + '%';
                        }

                    } else if (this.type == 2) {

                        pr_tax_val = parseFloat(this.rate);
                        pr_tax_rate = this.rate;

                    }
                }
            });
        }

        $('#net_cost').text(formatPurDecimal(unit_cost));
        $('#pro_tax').text(formatPurDecimal(pr_tax_val));
    });
	
    /* -----------------------
     * Edit Row Method 
     ----------------------- */

    $(document).on('click', '#editItem', function (){
        var row     = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id'), new_pr_tax = $('#ptax').val(), new_pr_tax_rate = {};
        var recieve = $('#qtyreceive').val();
         
        if (new_pr_tax) {
            $.each(tax_rates, function () {
                if (this.id == new_pr_tax) {
                    new_pr_tax_rate = this;
                }
            });
        }
		
        var new_pr_supplier = $('#psupplier').val(), new_pr_supplier_name = $('#psupplier option:selected').text();
        poitems[item_id].row.received 		= parseFloat(recieve);
        poitems[item_id].row.qty 			= parseFloat($('#pquantity').val()),
        poitems[item_id].row.real_unit_cost = parseFloat($('#pcost').val()),
		poitems[item_id].row.cost 			= parseFloat($('#pcost').val()),
        poitems[item_id].row.tax_rate 		= new_pr_tax,
        poitems[item_id].tax_rate 			= new_pr_tax_rate,
        poitems[item_id].row.discount 		= $('#pdiscount').val() ? $('#pdiscount').val() : '0',
        poitems[item_id].row.option 		= $('#poption').val(),
        poitems[item_id].supplier_id 		= $('#psupplier').val(),
        poitems[item_id].row.expiry 		= $('#pexpiry').val() ? $('#pexpiry').val() : '';
		
        localStorage.setItem('poitems', JSON.stringify(poitems));
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
		var item = poitems[item_id];
		if(item.options !== false) {
			$.each(item.options, function () {
				if(this.id == opt && this.cost != 0 && this.cost != '' && this.cost != null) {
					$('#pcost').val(this.cost);
					$("#net_cost").text(formatPurDecimal(this.cost));
				}
			});
		}
	});

    /* ------------------------------
     * Show manual item addition modal 
     ------------------------------- */
     $(document).on('click', '#addManually', function (e) {
        $('#mModal').appendTo("body").modal('show');
        return false;
    });

    /* --------------------------
     * Edit Row Quantity Method 
     -------------------------- */
    var old_row_qty;
    $(document).on("focus", '.rquantity, .received', function () {
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
        poitems[item_id].row.qty = new_qty;
        localStorage.setItem('poitems', JSON.stringify(poitems));
        loadItems();
		
    });
	
	$('.rquantity').bind('keypress', function (e) {
		if (e.keyCode == 13) {
			e.preventDefault();
			$("#add_item").focus();
		}
	});

	/* --------------------------
     * Edit Row Quantity Method 
     -------------------------- */
    var old_row_rqty;
    $(document).on("focus", '.received', function () {
        old_row_rqty = $(this).val();
    }).on("change", '.received', function () {
        var row = $(this).closest('tr');
		var new_qty = parseFloat($(this).val());
        if (!is_numeric($(this).val())) {
            $(this).val(old_row_rqty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
		if(new_qty > old_row_rqty) {
            bootbox.alert(lang.unexpected_value);
            $(this).val(old_row_rqty);
            return false;
        }
        var item_id = row.attr('data-item-id');
        poitems[item_id].row.received = new_qty;
        localStorage.setItem('poitems', JSON.stringify(poitems));
        loadItems();
    });
	
	$('.received').bind('keypress', function (e) {
		if (e.keyCode == 13) {
			e.preventDefault();
			$("#add_item").focus();
		}
	});

    /* --------------------------
     * Edit Row Cost Method 
     -------------------------- */
    var old_cost;
    $(document).on("focus", '.rcost', function () {
        old_cost = $(this).val();
    }).on("change", '.rcost', function () {
        var row = $(this).closest('tr');
        if(!is_numeric($(this).val())){
			if($(this).val() == ''){
				$(this).val(0);
			}else{
				$(this).val(old_cost);
				bootbox.alert(lang.unexpected_value);
			}
            return;
		}
        var new_cost = parseFloat($(this).val());
        item_id = row.attr('data-item-id');
        //poitems[item_id].row.cost = new_cost;
		poitems[item_id].row.real_unit_cost = new_cost;
		poitems[item_id].row.unit_cost = new_cost;
		poitems[item_id].row.cost = new_cost;
		poitems[item_id].row.net_cost = new_cost;
        localStorage.setItem('poitems', JSON.stringify(poitems));
        loadItems();
    });

	var old_price;
	$(document).on('focus', '.rprice', function(){
		old_price = $(this).val();
	}).on('change', '.rprice', function(){
		var row = $(this).closest('tr');
		if(!is_numeric($(this).val())){
			if($(this).val() == ''){
				$(this).val(0);
			}else{
				$(this).val(old_price);
				bootbox.alert(lang.unexpected_value);
			}
            return;
		}
		var new_price = parseFloat($(this).val());

        item_id = row.attr('data-item-id');
		poitems[item_id].row.price = new_price;
        localStorage.setItem('poitems', JSON.stringify(poitems));
        loadItems();
	});
    
    $(document).on("click", '#removeReadonly', function () { 
		$('#posupplier').select2('readonly', false); 
		return false;
	});
	
	$(document).on('change', '#postatus', function(){
		//var po_status = $(this).val();
		//localStorage.setItem('po_status', po_status);
		//loadItems();
		return;
	});
    
});
/* -----------------------
 * Misc Actions
 ----------------------- */
 
// hellper function for supplier if no localStorage value
function nsSupplier() {
    $('#posupplier').select2({
        minimumInputLength: 1,
        ajax: {
            url: site.base_url + "suppliers/suggestions",
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

function loadItems() {
    if (localStorage.getItem('poitems')) {
        var countEx=0;
        total = 0;
        count = 1;
        an = 1;
        product_tax = 0;
        total_qty = 0;
        invoice_tax = 0;
        product_discount = 0;
        order_discount = 0;
        total_discount = 0;
        $("#poTable tbody").empty();
        poitems = JSON.parse(localStorage.getItem('poitems'));
        var no_ = 1;
        var check_serial = false;
        var get_serial   = false;
        var exiting      = false;
        var getSerial    = '';
        var serial_array = [];
        var chk          = 0;
        var qtychk       = 0;
        var qtyrec       = 0;
        var postatus 	 = $('#postatus').val();
        $.each(poitems, function () {

            var item = this;
            var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
            poitems[item_id] = item;

            var product_id 		= item.row.id,
                item_type 		= item.row.type,
                combo_items 	= item.combo_items,
                item_cost 		= item.row.cost,
                item_qty 		= (item.row.type == 'service'?1:item.row.qty),
                item_bqty 		= item.row.quantity_balance,
                item_expiry 	= item.row.expiry,
                item_tax_method = item.row.tax_method,
                item_ds 		= item.row.discount,
                item_discount 	= 0,
                item_option 	= item.row.option,
                item_code 		= item.row.code,
                item_name		= item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;"),
                serial_no 		= item.row.serial,
                is_serial 		= item.row.is_serial,
                serial_item 	= item.row.serial_item;
            var qty_received 	= (item.row.received >= 0) ? item.row.received : item.row.qty;
            var item_supplier_part_no = item.row.supplier_part_no ? item.row.supplier_part_no : '';
            var supplier 		= localStorage.getItem('posupplier'), belong = false;
            var type 			= item.row.type;
            var exist 			= item.row.exist;
            var supplier_name 	= '';
            if(item.row.sepp == undefined){
                serial_exit = '';
            }else{
                serial_exit = item.row.sepp;
            }

            var supplier_id = item.supplier_id?item.supplier_id:'';
            if (supplier == item.row.supplier1) {
                belong = true;
            } else
            if (supplier == item.row.supplier2) {
                belong = true;
            } else
            if (supplier == item.row.supplier3) {
                belong = true;
            } else
            if (supplier == item.row.supplier4) {
                belong = true;
            } else
            if (supplier == item.row.supplier5) {
                belong = true;
            }
            if(serial_no != null && serial_no != ''){
                get_serial = true;
                getSerial = serial_no;
            }else{
                serial_no = serial_item;
            }

            if(is_serial == 1){
                check_serial = true;
                qtychk += parseFloat(item_qty);
                qtyrec += parseFloat(qty_received);
                var seri = (serial_no ? serial_no : serial_item);
                serial_array.push(seri);
            }
            if(serial_exit != ''){
                exiting = true;
            }
            var unit_cost 		= item.row.real_unit_cost;
            var last_cost 		= item.row.cost;
            var net_unit_cost 	= item.row.cost ? item.row.cost : 0;
            var checkNetCost 	= 'net_cost' in item.row;
            if(checkNetCost == false){
                net_unit_cost = item.row.cost?item.row.cost:0;
            }

            var ds = item_ds ? item_ds : '0';
            if (ds.indexOf("%") !== -1) {
                var pds = ds.split("%");
                if (!isNaN(pds[0])) {
                    item_discount = parseFloat(((net_unit_cost) * parseFloat(pds[0])) / 100);
                } else {
                    item_discount = ds;
                }
            } else {
                item_discount = ds;
            }

            product_discount 	+= formatPurDecimal(item_discount * item_qty);

            unit_cost 			= formatPurDecimal(unit_cost - item_discount);
            price 				= formatPurDecimal(item.row.price);
            var pr_tax 			= item.tax_rate;
            var pr_tax_val 		= 0, pr_tax_rate = 0, pr_price_tax = 0;
            if (site.settings.tax1 == 1) {
                if (pr_tax !== false) {
                    if (pr_tax.type == 1) {
                        if (item_tax_method == '0') {
                            pr_tax_val = formatPurDecimal(((net_unit_cost) * parseFloat(pr_tax.rate)) / (100 + parseFloat(pr_tax.rate)));
                            pr_price_tax = formatPurDecimal(((price) * parseFloat(pr_tax.rate)) / (100 + parseFloat(pr_tax.rate)));
                            pr_tax_rate = formatPurDecimal(pr_tax.rate) + '%';
                        } else {
                            pr_tax_val = formatPurDecimal(((unit_cost) * parseFloat(pr_tax.rate)) / 100);
                            pr_price_tax = formatPurDecimal(((price) * parseFloat(pr_tax.rate)) / 100);
                            pr_tax_rate = formatPurDecimal(pr_tax.rate) + '%';
                        }
                    } else if (pr_tax.type == 2) {
                        pr_tax_val = parseFloat(pr_tax.rate);
                        pr_tax_rate = pr_tax.rate;
                    }
                    product_tax += pr_tax_val * item_qty;
                }
            }

            price 				= price + pr_price_tax;
            narmal_price 		= formatPurDecimal(price - pr_price_tax);
            item_cost 			= item_tax_method == 0 ? formatPurDecimal(net_unit_cost - pr_tax_val) : formatPurDecimal(net_unit_cost);
            unit_cost 			= formatPurDecimal(unit_cost+item_discount);
            var sel_opt 		= '';
            var option_qty_unit = '';

            $.each(item.options, function () {
                if(this.id == item_option) {
                    sel_opt = this.name;
                    option_qty_unit = this.qty_unit;
                    //item_cost = this.cost * option_qty_unit;
                }
            });

            if(option_qty_unit != 0){
                item_cost = item_cost;
            }
            var stock_in_hand = formatPurDecimal(item.row.quantity);
            if(isNaN(stock_in_hand)){
                stock_in_hand = 0;
            }
//alert(exist);
            if(isNaN(exist)){
                exist=0;
            }
            else{
                countEx+=parseInt(exist);
            }


            var row_no = (new Date).getTime();
            var newTr = $('<tr '+(is_serial == 1 ? (serial_exit == '' ? 'style="color:blue !important;"' : '') : '')+' id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '" serial_exit="'+serial_exit+'" ></tr>');


            tr_html = '<td class="text-right"><span class="text-center">#'+ no_ +'</td>';

            tr_html += '<td><input name="product_id[]" type="hidden" is_serial="'+is_serial+'" class="rid " value="' + product_id + '"><input name="product[]" type="hidden" class="rcode '+(exist == 1 ? 'exist':'')+'" value="' + item_code + '"><input name="product_name[]" type="hidden" class="rname '+(exist == 1 ? 'exist':'')+'" value="' + item_name + '"><input name="type[]" type="hidden" class="rtype" value="' + type + '"><input name="product_option[]" type="hidden" class="roption" value="' + item_option + '"><input name="part_no[]" type="hidden" class="rpart_no" value="' + item_supplier_part_no + '"><input name="rsupplier_id[]" type="hidden" class="rsupplier_id" value="' + supplier_id + '"><span class="sname" id="name_' + row_no + '">' + item_name + ' (' + item_code + ')'+(sel_opt != '' ? ' ('+sel_opt+')' : '')+' ' +
                '' +
                '<span class="">'+ supplier_name +'</span><span class="label label-default">'+item_supplier_part_no+'</span></span> <i class="pull-right fa fa-edit tip edit" id="' + row_no + '" data-item="' + item_id + '" title="Edit" style="cursor:pointer;"></i>'+ (exist == 1 ? '<a href="'+ site.base_url+'products/add/0/'+item_code+'" ><i class="pull-right fa fa-plus tip add" id="' + row_no + '" data-item="' + item_id + '" title="Add" style="cursor:pointer;"></i></a><input type="hidden" name="existA" value="1">' : '') +'</td>';

            if (site.settings.product_expiry == 1){
                tr_html += '<td><input class="form-control date rexpiry" name="expiry[]" type="text" value="' + item_expiry + '" data-id="' + row_no + '" data-item="' + item_id + '" id="expiry_' + row_no + '"></td>';
            }

            /* Price */
            //tr_html += '<td class="text-center">' + price + '</td>';
            tr_html += '<td class="text-right">' + narmal_price + '<input type="hidden" value="'+narmal_price+'" name="price[]"></td>';

            /* Net Unit Cost */


            tr_html += '<td class="text-right"><input class="form-control text-center rcost" name="net_cost[]" type="text" id="cost_' + row_no + '" value="' + formatPurDecimal(item_cost) + '"><input class="rucost" name="unit_cost[]" type="hidden" value="' + net_unit_cost + '"><input class="realucost" name="real_unit_cost[]" type="hidden" value="' + net_unit_cost + '"></td>';

            tr_html += '<td><input name="quantity_balance[]" type="hidden" class="rbqty" value="' + item_bqty + '"><input class="form-control text-center rquantity" name="quantity[]" type="text" value="' + formatPurDecimal(item_qty) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();"></td>';

            /* Stock In Hand */
            tr_html += '<td class="text-right"><input class="form-control input-sm text-right rstock" name="rstock[]" type="hidden" id="stock_' + stock_in_hand + '" value="' + stock_in_hand + '"><input class="rstock" name="rstock[]" type="hidden" value="' + stock_in_hand + '"><input class="rstock" name="rstock[]" type="hidden" value="' + stock_in_hand + '"><span class="text-right scost" id="sstock_' + row_no + '">' + stock_in_hand + '</span></td>';

            if (site.settings.product_discount == 1) {
                tr_html += '<td class="text-right"><input class="form-control input-sm rdiscount" name="product_discount[]" type="hidden" id="discount_' + row_no + '" value="' + (item_ds) + '"><span class="text-right sdiscount text-danger" id="sdiscount_' + row_no + '">' + (item_ds ? '(' + item_ds + ')' : '') + ' ' + formatPurDecimal(item_discount * item_qty) + '</span></td>';
            }

            if (site.settings.tax1 == 1) {
                tr_html += '<td class="text-right"><input class="form-control input-sm text-right rproduct_tax" name="product_tax[]" type="hidden" id="product_tax_' + row_no + '" value="' + pr_tax.id + '"><span class="text-right sproduct_tax" id="sproduct_tax_' + row_no + '">' + (pr_tax_rate ? '(' + pr_tax_rate + ')' : '') + ' ' + formatPurDecimal(pr_tax_val * item_qty) + '</span></td>';
                //tr_html += '<input class="form-control input-sm text-right rproduct_tax" name="product_tax[]" type="hidden" id="product_tax_' + row_no + '" value="' + pr_tax.id + '">';
            }

            /* Sub Total */

            tr_html += '<td class="text-right"><span class="text-right ssubtotal" id="subtotal_' + row_no + '">' + formatPurDecimal((parseFloat(item_cost - item_discount) + parseFloat(pr_tax_val)) * parseFloat(item_qty)) + '</span></td>';

            tr_html += '<td class="text-center"><i class="fa fa-times tip podel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
            newTr.html(tr_html);
            $('#cEx').html('<input type="hidden" name="cEx" id="cExist" value="'+countEx+'" >');
            newTr.prependTo("#poTable");

            /* Total */
            total += formatPurDecimal((parseFloat(item_cost - item_discount) + parseFloat(pr_tax_val)) * parseFloat(item_qty));

            count += parseFloat(item_qty);
            an++;
            if(!belong)
                $('#row_' + row_no).addClass('danger');
            no_++;

        });

        var col = 4;
        if (site.settings.product_expiry == 1) { col++; }
        var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="'+col+'">Total</th><th class="text-center">' + formatNumber(parseFloat(count) - 1) + '</th><th class="text-center"></th>';
        /*if (po_edit) {
            tfoot += '<th class="rec_con"></th>';
        }*/
        if (site.settings.product_discount == 1) {
            tfoot += '<th class="text-right">'+formatPurDecimal(product_discount)+'</th>';
        }
        if (site.settings.tax1 == 1) {
            tfoot += '<th class="text-right">'+formatPurDecimal(product_tax)+'</th>';
        }
        tfoot += '<th class="text-right">'+ formatPurDecimal(total)+'</th><th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
        $('#poTable tfoot').html(tfoot);

        // Order level discount calculations
        if (podiscount = localStorage.getItem('podiscount')) {
            var ds = podiscount;
            if (ds.indexOf("%") !== -1) {
                var pds = ds.split("%");
                if (!isNaN(pds[0])) {
                    order_discount = ((total) * parseFloat(pds[0])) / 100;
                } else {
                    order_discount = parseFloat(ds);
                }
            } else {
                order_discount = parseFloat(ds);
            }
        }

        // Order level tax calculations
        if (site.settings.tax2 != 0) {
            if (potax2 = localStorage.getItem('potax2')) {
                $.each(tax_rates, function () {
                    if (this.id == potax2) {
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
        var gtotal = ((total + invoice_tax) - order_discount) + shipping;
        $('#total').text(formatPurDecimal(total));
        $('#titems').text((an-1)+' ('+(parseFloat(count)-1)+')');
        $('#tds').text(formatPurDecimal(order_discount));
        if (site.settings.tax1) {
            $('#ttax1').text(formatPurDecimal(product_tax));
        }
        if (site.settings.tax2 != 0) {
            $('#ttax2').text(formatPurDecimal(invoice_tax));
        }
        $('#gtotal').text(formatPurDecimal(gtotal));
        if (an > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
            $("html, body").animate({scrollTop: $('#sticker').offset().top}, 500);
            $(window).scrollTop($(window).scrollTop() + 1);
        }
        if(po_edit){
            $('#postatus').trigger('change');
        }
    }
}

$('.net_cost, .quantity').live('change',function(){
	var row = $(this).parent().parent();
	var net_price = $('.net_cost').val()-0;
	var quantity = row.find('.quantity').val()-0;
	var tax_per = row.find('.tax_percent').val();
	var tax_pay = 0;
	if(tax_per != '') {
		var rate = tax_per.split('%');
		tax_pay = ((net_price * quantity) * (rate[0]/100));
		row.find('.getTax').val(tax_pay);
		row.find('.sproduct_tax').text('('+tax_per+') '+formatPurDecimal(tax_pay));
	}
	var getTotal = formatPurDecimal(((parseFloat(net_price) * parseFloat(quantity))  + parseFloat(tax_pay)));
	row.find('.get_total').text(getTotal);
});

/* -----------------------------
 * Add Purchase Iten Function
 * @param {json} item
 * @returns {Boolean}
 ---------------------------- */
 function add_purchase_item(item) {
    if (count == 1) {
        poitems = {};
        if ($('#posupplier').val()) {
            $('#posupplier').select2("readonly", true);
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
    if (poitems[item_id]) {
        poitems[item_id].row.qty = parseFloat(poitems[item_id].row.qty) + 1;
    } else {
        poitems[item_id] = item;
    }
    
    localStorage.setItem('poitems', JSON.stringify(poitems));
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