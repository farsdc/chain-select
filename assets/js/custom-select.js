jQuery(document).ready(function ($) {
    $('#chain-select-category').change(function () {
        var category_id = $(this).val();
        if (category_id) {
            $.ajax({
                type: 'POST',
                url: chain_select_ajax_obj.ajax_url,
                data: {
                    action: 'chain_select_get_attributes',
                    category_id: category_id
                },
                success: function (response) {
                    $('#chain-select-attributes').html(response);
                    $('#chain-select-vendors').html('');
                }
            });
        } else {
            $('#chain-select-attributes').html('');
            $('#chain-select-vendors').html('');
        }
    });

    $('#chain-select-province').change(function () {
        var province_id = $(this).val();
        if (province_id) {
            $.ajax({
                type: 'POST',
                url: chain_select_ajax_obj.ajax_url,
                data: {
                    action: 'get_cities',
                    province_id: province_id
                },
                success: function (response) {
                    if (response.success) {
                        var options = '<option value="">' + chain_select_ajax_obj.select_city + '</option>';
                        $.each(response.data.cities, function (index, city) {
                            options += '<option value="' + city.id + '">' + city.title + '</option>';
                        });
                        $('#chain-select-city').html(options);
                    }
                }
            });
        } else {
            $('#chain-select-city').html('<option value="">' + chain_select_ajax_obj.select_city + '</option>');
        }
    });

    $('#chain-select-attributes').on('change', 'select', function () {
        var attributes = {};
        $('#chain-select-attributes select').each(function () {
            var key = $(this).attr('name').replace('attributes[', '').replace(']', '');
            var value = $(this).val();
            attributes[key] = value;
        });

        $.ajax({
            type: 'POST',
            url: chain_select_ajax_obj.ajax_url,
            data: {
                action: 'chain_select_get_vendors',
                attributes: attributes,
                province_id: $('#chain-select-province').val(),
                city_id: $('#chain-select-city').val()
            },
            success: function (response) {
                $('#chain-select-vendors').html(response);
            }
        });
    });

    $('#chain-select-show-products').click(function (e) {
        e.preventDefault();

        var category_id = $('#chain-select-category').val();
        var vendor_id = $('#chain-select-vendor-select').val();
        var attributes = {};
        $('#chain-select-attributes select').each(function () {
            var key = $(this).attr('name').replace('attributes[', '').replace(']', '');
            var value = $(this).val();
            attributes[key] = value;
        });

        $.ajax({
            type: 'POST',
            url: chain_select_ajax_obj.ajax_url,
            data: {
                action: 'chain_select_get_products',
                category_id: category_id,
                vendor_id: vendor_id,
                attributes: attributes,
                province_id: $('#chain-select-province').val(),
                city_id: $('#chain-select-city').val()
            },
            success: function (response) {
                if (response.success) {
                    $('#chain-select-product-list').html(response.data);
                } else {
                    $('#chain-select-product-list').html('<p>' + response.data.message + '</p>');
                }
            }
        });
    });
});
