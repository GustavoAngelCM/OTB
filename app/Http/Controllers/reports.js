let reportObj = {
    "month": null,
    "payment": null,
    "fine": null,
    "assistance": null
};

let users = [];

$(document).ready(() => {
    onChangeStatesToReports();
    getMonths();
    getUsersAPI();
    $('#cancelled').val('on');
});

async function getMonths()
{
    $('#responseReport').html('<tr><th colspan="4" rowspan="2" style="text-align: center;"><i class="zmdi zmdi-spinner zmdi-hc-spin" style="font-size: 5rem;"></i></th></tr>');
    try {
        const {data} = await conectAPI('months', 'GET', null, true);
        const monthsElement = $('#months');
        data.forEach((month, i) => {
            if (i === 0) {
                reportObj = {
                    ...reportObj,
                    "month": month.date
                };
            }
            monthsElement.append(`<option value="${month.date}">${month.parserDate}</option>`)
        });
    } catch (error) {
        $('#responseReport').html('<tr><th colspan="4" rowspan="2" style="text-align: center; color: red; font-size: x-large;">Error al consultar con el servidor</th></tr>');
    }
}

function onChangeStatesToReports()
{
    const GroupDataElement = $('#groupData');
    const MonthsElement = $('#months');
    const CancelledElement = $('#cancelled');   
    $('#cancelled[type="checkbox"]').attr("checked", "checked");
    GroupDataElement.change(function(e){
        const selectedOption = $('#groupData option:selected').val();
        if (selectedOption === '0') {
            $(this).parent().parent().css('width', '100%');
            MonthsElement.parent().parent().hide();
            CancelledElement.parent().parent().parent().hide();
        } else {
            $(this).parent().parent().removeAttr('style');
            MonthsElement.parent().parent().show();
            CancelledElement.parent().parent().parent().show();
        }
        const boleanCancelled = (CancelledElement.val() === 'on') ? true : false;
        switch (selectedOption) {
            case '0':
                reportObj = {
                    ...reportObj,
                    "payment": null,
                    "fine": null,
                    "assistance": null
                };
                break;
            case '1':
                reportObj = {
                    ...reportObj,
                    "payment": boleanCancelled,
                    "fine": null,
                    "assistance": null
                };
                break;
            case '2':
                reportObj = {
                    ...reportObj,
                    "payment": null,
                    "fine": boleanCancelled,
                    "assistance": null
                };
                break;
            case '3':
                reportObj = {
                    ...reportObj,
                    "payment": null,
                    "fine": null,
                    "assistance": boleanCancelled
                };
                break;
            default:
                reportObj = {
                    ...reportObj,
                    "payment": null,
                    "fine": null,
                    "assistance": null
                };
                break;
        }
        getUsersAPI();
    });
    MonthsElement.change(function(e){
        reportObj = {
            ...reportObj,
            "month": e.target.value
        };
        getUsersAPI();
    });
    CancelledElement.click(function(e){
        const selectedOption = $('#groupData option:selected').val();
        $(this).val(($(this).val() === 'on') ? 'off' : 'on');
        const boleanCancelled = ($(this).val() === 'on') ? true : false;
        switch (selectedOption) {
            case '1':
                reportObj = {
                    ...reportObj,
                    "payment": boleanCancelled
                };
                break;
            case '2':
                reportObj = {
                    ...reportObj,
                    "fine": boleanCancelled
                };
                break;
            case '3':
                reportObj = {
                    ...reportObj,
                    "assistance": boleanCancelled
                };
                break;
        }
        getUsersAPI();
    });
}

async function getUsersAPI()
{
    if ($('#responseReport').children().eq(0).children().length > 1) {
        $('#example').DataTable().destroy();
    }
    $('#responseReport').html('<tr><th colspan="4" rowspan="2" style="text-align: center;"><i class="zmdi zmdi-spinner zmdi-hc-spin" style="font-size: 5rem;"></i></th></tr>');
    try {
        const {data} = await conectAPI('reports', 'POST', JSON.stringify(reportObj), true);
        users = data;        
        if (users.length > 0) {
            columnsrender();
            renderTable();
            $('#example').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ]
            });
        } else {
            $('#minimumAmount').hide(800);
            $('#totalAmount').hide(800);
            $('#maximumAmount').hide(800);
            $('#responseReport').html('<tr><th colspan="4" rowspan="2" style="text-align: center; color: red; font-size: x-large;">No existen resultados</th></tr>')
        }
    } catch (error) {
        console.log(error);
        $('#minimumAmount').hide(800);
        $('#totalAmount').hide(800);
        $('#maximumAmount').hide(800);
        $('#responseReport').html('<tr><th colspan="4" rowspan="2" style="text-align: center; color: red; font-size: x-large;">Error al consultar con el servidor</th></tr>');
    }
}

function columnsrender()
{
    $('#minimumAmount').hide(800);
    $('#totalAmount').hide(800);
    $('#maximumAmount').hide(800);
    const columnsElement = $('#columnsTable');
    const columnUser = users[0];
    columnsElement.html(null);
    columnsElement.append([
        getSimpleElement('th', false).append('N.º'),
        getSimpleElement('th', false).append('Nombre'),
        getSimpleElement('th', false).append('Orden'),
        getSimpleElement('th', false).append('Medidor')
    ]);
    if(columnUser.key_transaction){
        columnsElement.append(getSimpleElement('th', false).append('Recibo'));
    }
    if (columnUser.mount) {
        columnsElement.append(getSimpleElement('th', false).append('Monto'));
        let total = users.reduce((total, user) => total + parseFloat(user.mount), 0);
        let orderedPayments = users.sort((userA, userB) => userA.mount - userB.mount);
        $('#minimumAmount').html(orderedPayments[0].mount);
        $('#totalAmount').html(total);
        $('#maximumAmount').html(orderedPayments[orderedPayments.length - 1].mount);
        $('#minimumAmount').show(800);
        $('#totalAmount').show(800);
        $('#maximumAmount').show(800);
    }
    if(columnUser.name_event){
        columnsElement.append(getSimpleElement('th', false).append('Evento'));
    }    
}

function renderTable()
{
    const reportElement = $('#responseReport');
    reportElement.html(null);
    users.forEach((user, i) => {
        const userN = getSimpleElement('td', false).append(i+1);
        const userName = getSimpleElement('td', false).append(user.fullName);
        const userOrder = getSimpleElement('td', false).append(user.order_gauge);
        const userNumber = getSimpleElement('td', false).append(user.number_gauge);
        const row = getSimpleElement('tr', false).append([userN, userName, userOrder, userNumber]);
        if(user.key_transaction){
            row.append(getSimpleElement('td', false).append(user.key_transaction));
        }
        if (user.mount) {
            row.append(getSimpleElement('td', false).append(user.mount))
        }
        if (user.name_event) {
            row.append(getSimpleElement('td', false).append(user.name_event))
        }
        reportElement.append(row);
    });
}