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
    //return  '<button name="btn_send_wa" id="btn_send_wa" class="form-submit-button" onclick="WaSender.sendWa("' + rowIndex + '");">Send WA</button>';
    return '<br/><button class="form-submit-button" onclick="WaSender.getInstance().sendWa(' + rowIndex + ');">Send WA</button>';//'tes' + rowIndex;
};

WaSender.prototype.sendWa = function (rowIndex)
{
    let waData = this._waData[rowIndex];

    let text = `
Assalamu'alaikum Wr. Wb. Salam sejahtera bagi kita semua. \n
Yang kami hormati Bapak/Ibu orang tua/wali dari *${waData.subjectName}*, \n
Kami dari panitia Skripsi Jurusan Teknologi Informasi bermaksud memberitahukan kepada Bapak/Ibu bahwasannya putra/putri Anda tersebut diatas saat ini baru melaksanakan progress bimbingan sebanyak *${waData.param} kali*. Perlu Bapak/Ibu ketahui, jumlah tersebut termasuk jumlah bimbingan *dibawah standar* dimana seharusnya sampai pada saat ini putra/putri Anda setidaknya sudah harus bimbingan *sebanyak 5 kali*. Berkaitan dengan hal tersebut, kami mohon perhatian Bapak/Ibu agar putra/putri Anda bisa lebih giat mengerkajan skripsi agar dapat lulus tepat waktu. \n
Demikian yang dapat kami sampaikan, atas perhatian Bapak/Ibu kami ucapkan terima kasih. \n
Wassalamu'alaikum Wr. Wb. \n
Hormat kami, \n\n
Panitia Skripsi 2020 \n
\n---
*Catatan*: Anda tidak perlu membalas, karena pesan ini di-generate oleh sistem.`;

    let encodedText = encodeURI(text);

    let recipientNumber = waData.recipient.replace('0', '+62');

    let waUrl = `https://api.whatsapp.com/send?phone=${recipientNumber}&text=${encodedText} `;

    window.open(waUrl);
};

function wa_sender()
{
    let sender = WaSender.getInstance();

    sender.init();
}

// TODO: Merge this with WaSender.js
wa_sender();