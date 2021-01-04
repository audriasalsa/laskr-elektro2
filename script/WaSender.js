function WaData()
{
    this.subjectName = '';
    this.recipient = '';
    this.param = '';
}

function WaSender()
{
    this._actionColumnIndex = 7;
    this._subjectNameColumnIndex = 2;
    this._recipientColumnIndex = 4;
    this._paramColumnIndex = 5;
    this._messageText = '';

    this._waData = [];
}

WaSender._instance = null;

WaSender.getInstance = function()
{
    if(WaSender._instance == null)
    {
        WaSender._instance = new WaSender();
    }

    return WaSender._instance;
};

WaSender.prototype.setActionColumnIndex = function(actionColumnIndex)
{
    this._actionColumnIndex = actionColumnIndex;
}

WaSender.prototype.setSubjectNameColumnIndex = function(subjectNameColumnIndex)
{
    this._subjectNameColumnIndex = subjectNameColumnIndex;
}

WaSender.prototype.setRecipientColumnIndex = function(recipientColumnIndex)
{
    this._recipientColumnIndex = recipientColumnIndex;
}

WaSender.prototype.setParamColumnIndex = function(paramColumnIndex)
{
    this._paramColumnIndex = paramColumnIndex;
}

WaSender.prototype.setMessageText = function(messageText)
{
    this._messageText = messageText;
}

WaSender.prototype.init = function ()
{
    let tableRows = document.getElementsByTagName('tr');

    let found = 0;

    for(let i = 0; i < tableRows.length; i++)
    {
        let tds = tableRows[i].getElementsByTagName('td');

        if (tds.length === (this._actionColumnIndex + 1))
        {
            tds[(this._actionColumnIndex)].innerHTML += this._createSenderButton(found);

            let data = new WaData();
            data.recipient = tds[(this._recipientColumnIndex)].innerText;
            data.subjectName = tds[(this._subjectNameColumnIndex)].innerText;
            data.param = tds[(this._paramColumnIndex)].innerText;

            this._waData.push(data);

            found++;
        }
    }
};

WaSender.prototype._createSenderButton = function (rowIndex)
{
    return '<br/><button class="form-submit-button" onclick="WaSender.getInstance().sendWa(' + rowIndex + ');">Send WA</button>';
};

WaSender._cleanPhoneNumber = function (phoneStr)
{
    phoneStr = WaSender._replaceAll(phoneStr, '-', '');
    phoneStr = WaSender._replaceAll(phoneStr, ' ', '');

    const countryCode = '+62';

    let firstChar = phoneStr.substring(0, 1);

    if(firstChar === '0')
        return countryCode + phoneStr.substr(1);

    let first3Chars = phoneStr.substring(0, 3);

    if(first3Chars === countryCode)
        return  phoneStr;

    let first2Chars = phoneStr.substring(0, 2);

    if(first2Chars === '62')
        return '+' + phoneStr;

    return countryCode + phoneStr;
};

WaSender._replaceAll = function(str, find, replace) {
    return str.replace(new RegExp(find, 'g'), replace);
};

WaSender.prototype.sendWa = function (rowIndex)
{
    let waData = this._waData[rowIndex];

    let text = this._messageText;

    text = text.replace('{{subjectName}}', waData.subjectName);
    text = text.replace('{{param}}', waData.param);

    let encodedText = encodeURI(text);

    let recipientNumber = WaSender._cleanPhoneNumber(waData.recipient);

    let waUrl = `https://api.whatsapp.com/send?phone=${recipientNumber}&text=${encodedText} `;

    window.open(waUrl);
};