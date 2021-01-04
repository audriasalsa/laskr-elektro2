function wa_sender()
{
    let sender = WaSender.getInstance();

    sender.setActionColumnIndex(8);
    sender.setRecipientColumnIndex(2);
    sender.setMessageText(document.getElementById('txa_wa_message').value);
    sender.setSubjectNameColumnIndex(1);
    sender.setParamColumnIndex(7);

    sender.init();
}

wa_sender();