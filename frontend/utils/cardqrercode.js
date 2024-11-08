export { getQrCode };
// Get_CRC8   CRC8 CRC8 CRC8
function FindCRC(data) {
  //data=>array
  let CRC = 0;
  let genPoly = 0x8c;
  for (let i = 0; i < data.length; i++) {
    CRC ^= data[i];
    CRC &= 0xff; //保证CRC余码输出为1字节。
    for (let j = 0; j < 8; j++) {
      if ((CRC & 0x01) != 0) {
        CRC = (CRC >> 1) ^ genPoly;
        CRC &= 0xff; //保证CRC余码输出为1字节。
      } else {
        CRC >>= 1;
      }
    }
  }
  CRC &= 0xff; //保证CRC余码输出为1字节。
  return CRC;
}

//16进制转10进制数组
function HEXToBytes(sHEX) {
  var ilen = sHEX.length;
  if (ilen % 2 == 1) {
    sHEX = "0" + sHEX;
  }
  ilen = sHEX.length;
  var bytes = new Array(ilen / 2);
  // console.log(bytes)
  var index = 0;
  var tmp = "";
  for (var i = 0; i < ilen; i = i + 2) {
    tmp = sHEX.substring(i, i + 2);
    // console.log(tmp)
    bytes[index] = parseInt(tmp, 16);
    index++;
  }
  return bytes;
}

function ByteToHEX(bytes) {
  var ilen = bytes.length;
  var sHEXList = new Array(ilen);
  var chr = "";
  for (var i = 0; i < ilen; i++) {
    chr = bytes[i].toString(16);
    if (chr.length == 1) chr = "0" + chr;
    sHEXList[i] = chr;
  }
  return sHEXList.join("");
}

//身份证正则
function isCardID(sId) {
  let id =
    /^[1-9][0-9]{5}(19|20)[0-9]{2}((01|03|05|07|08|10|12)(0[1-9]|[1-2][0-9]|31)|(04|06|09|11)(0[1-9]|[1-2][0-9]|30)|02(0[1-9]|[1-2][0-9]))[0-9]{3}([0-9]|x|X)$/;
  if (id.test(sId) === true) {
    return true;
  } else {
    return "身份证号不正确";
  }
}

function dec2hex(str) {
  var dec = str.toString().split(""),
    sum = [],
    hex = [],
    i,
    s;
  while (dec.length) {
    s = 1 * dec.shift();
    for (i = 0; s || i < sum.length; i++) {
      s += (sum[i] || 0) * 10;
      sum[i] = s % 16;
      s = (s - sum[i]) / 16;
    }
  }
  while (sum.length) {
    hex.push(sum.pop().toString(16));
  }
  return hex.join("");
}

function RC4() {
  /**
   * RC4 加解密
   * @param data
   * @param key
   * @return
   */
  function encry_decry_RC4(data, key) {
    if (data == null || key == null) {
      return null;
    }
    let b_data = HEXToBytes(data);
    let b_Key = HEXToBytes(key);
    let b_Result = RC4Base(b_data, b_Key);
    return ByteToHEX(b_Result);
  }

  /**
   * RC4 加解密
   * @param data
   * @param key
   * @return
   */
  function encry_decry_RC41(data, key) {
    if (data == null || key == null) {
      return null;
    }
    return RC4Base(data, key);
  }

  /**
   * 初始化RC4的密钥序列
   *
   * @param b_key RC4的密钥
   * @return
   */
  function initKey(b_key) {
    let S = new Array(256);

    for (let i = 0; i < 256; i++) {
      S[i] = i;
    }
    let index2 = 0;

    if (b_key == null || b_key.length == 0) {
      return null;
    }
    let key_len = b_key.length;
    let tmp;

    for (let i = 0; i < 256; i++) {
      index2 = (index2 + S[i] + ubyte(b_key[i % key_len])) % 256;
      tmp = S[index2];
      S[index2] = S[i];
      S[i] = tmp;
    }
    return S;
  }

  /**
   * 返回无符号的整形值
   *
   * @param b
   * @return
   */
  function ubyte(b) {
    return b & 0x00ff;
  }

  /**
   * RC4 加解密
   *
   * @param input 需要加密/解密的数据
   * @param mKkey RC4的密钥
   * @return 解密/加密 后的数据
   */
  function RC4Base(input, mKkey) {
    let i = 0;
    let j = 0;
    let S = initKey(mKkey);
    let counter = 0;
    let temp = 0;
    let data_len = input.length;
    let PRGA = 0;

    let result = new Array(data_len);
    let VECTOR_LENGTH = 256;
    for (counter = 0; counter < data_len; counter++) {
      i = (i + 1) % VECTOR_LENGTH;
      j = (j + S[i]) % VECTOR_LENGTH;
      temp = S[i];
      S[i] = S[j];
      S[j] = temp;

      PRGA = S[(S[i] + S[j]) % VECTOR_LENGTH];

      result[counter] = PRGA ^ ubyte(input[counter]);
    }
    return result;
  }

  return {
    encry_decry_RC4,
    encry_decry_RC41,
  };
}
//在原数组上生成特定长度的数组，并添加特定字符
function setARRStart(arr, par, length) {
  let Arrays = [];
  // console.log(arr.length);
  let a = arr.length;
  if (a < length) {
    let b = length - a;
    for (let i = 0; i < b; i++) {
      Arrays[i] = par;
    }
    Arrays = [...Arrays, ...arr];
  } else {
    Arrays = [...arr];
  }
  return Arrays;
}

/**
 *  * 调用getQrCode(有效期时间戳,卡号)  卡号只能为数字，卡号为身份证号码的时候要把省份证号码的  X   改为  A
 * @param {Date} date   有效期  indate
 * @param {number} CardNum
 * @param {number} cardType ---> 1 number 2 IDcard
 * @returns  promise
 */
function getQrCode(date, CardNum, cardType) {
  // 1  先把时间转成10进制数组
  return new Promise((resove, reject) => {
    let year = date.getFullYear() - 2018;
    let month = date.getMonth() + 1;
    let day = date.getDate();
    let hour = date.getHours();
    let minute = date.getMinutes();
    let second = date.getSeconds();
    console.log(year, month, day, hour, minute, second);
    let Tbit2 =
      second.toString(2).padStart(6, 0) +
      minute.toString(2).padStart(6, 0) +
      hour.toString(2).padStart(5, 0) +
      day.toString(2).padStart(5, 0) +
      month.toString(2).padStart(4, 0) +
      year.toString(2).padStart(6, 0);
    Tbit2 = parseInt(Tbit2, 2); //2 bit=>10 bit
    let bit16Time = Tbit2.toString(16); //10bit =>16bit
    console.log(bit16Time);
    let arrTime = HEXToBytes(bit16Time); //16bit => 10bit Arr []
    console.log(arrTime);
    /*
     *   日期
     */
    arrTime = setARRStart(arrTime, 0, 4); // 长度为4的 数组
    // 卡号
    let card, arrCard;
    if (cardType == 1) {
      let num = CardNum + "";
      if (!num) {
        reject("卡号不能为空");
      }
      if (cardType == 1) {
        if (!/^[0-9]*$/.test(num)) {
          reject("卡号只能为数字");
        }
        if (num.length >= 22) {
          if (num.length > 22) {
            reject("数字不能大于4722366482869645213695");
          }
          if (num > "4722366482869645213695") {
            reject("数字不能大于4722366482869645213695");
          }
        }
      }
      card = dec2hex(CardNum);
      console.log(card);
      // card=card.toString(16);
      arrCard = HEXToBytes(card);
    } else {
      if (isCardID(CardNum) == true) {
        let card_data = (CardNum + "").toUpperCase().replace(/X/, "A");
        arrCard = HEXToBytes(card_data);
      } else {
        reject(isCardID(CardNum));
      }
    }
    //16bit => 10bit Arr []
    /*
     *   卡号  9位 数组
     */
    arrCard = setARRStart(arrCard, 0, 9); // 长度为9的 数组
    // 合并 =》 13位数组
    arrCard = [...arrCard, ...arrTime]; //
    /*
     *   校验
     */
    let checktxt = FindCRC(arrCard);
    // arrCard.push(checktxt)//14位数组
    console.log(arrCard);
    let par1 = ByteToHEX(arrCard);
    let outstr =
      RC4().encry_decry_RC4(
        par1,
        "3165333062336563306636333438373439353665323736323764666332633436"
      ) + checktxt.toString(16).padStart(2, 0);

    let das = RC4().encry_decry_RC4(
      par1,
      "3165333062336563306636333438373439353665323736323764666332633436"
    );

    console.log(par1, das);
    console.log(
      das,
      RC4().encry_decry_RC4(
        das,
        "3165333062336563306636333438373439353665323736323764666332633436"
      )
    );
    // decryQrCode('2e' + outstr)
    resove("2e" + outstr);
  });
}

function hexToBytes(hex) {
  const bytes = [];
  for (let i = 0; i < hex.length; i += 2) {
    bytes.push(parseInt(hex.slice(i, i + 2), 16));
  }
  return bytes;
}
function bytesToHex(bytes) {
  return bytes.map((byte) => byte.toString(16).padStart(2, "0")).join("");
}

function decryQrCode(str) {
  let par1 = str.slice(2, str.length - 2).toLowerCase();
  console.log(par1);
  let outstr = RC4().encry_decry_RC4(
    par1,
    "3165333062336563306636333438373439353665323736323764666332633436"
  );
  let arrCard = hexToBytes(outstr);
  console.log(arrCard);

  let cardbytes = arrCard.slice(0, 9);
  let arrTime = arrCard.slice(9, 14);
  console.log(cardbytes, arrTime);

  let ccard = bytesToHex(cardbytes.filter((item) => item !== 0));
  let timea = bytesToHex(arrTime.filter((item) => item !== 0));
  console.log(ccard, timea);

  let time = parseInt(timea, 16).toString(2).padStart(32, 0);
  let year = parseInt(time.slice(-6), 2) + 2018;
  let month = parseInt(time.slice(-10, -6), 2);
  let day = parseInt(time.slice(-15, -10), 2);
  let hour = parseInt(time.slice(-20, -15), 2);
  let minute = parseInt(time.slice(-26, -20), 2);
  let second = parseInt(time.slice(0, 6), 2);
  console.log(year, month, day, hour, minute, second);

  let card = parseInt(ccard, 16);

  return {
    card,
    date: new Date(year, month, day, hour, minute, second),
    datetext: `${year}-${month}-${day} ${hour}:${minute}:${second}`,
  };
  // let par1 = ByteToHEX(str);
}
