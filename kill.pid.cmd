netstat -ano | find ":443" | find "LISTENING"
taskkill /F /IM vmware-hostd.exe