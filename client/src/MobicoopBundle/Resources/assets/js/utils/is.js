const EMAIL_REGEXP = /[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/;

const PHONE_REGEXP = /(?:(?:\+|00)[0-9]{2}[\s.-]{0,3}(?:\(0\)[\s.-]{0,3})?|0)[1-9](?:(?:[\s.-]?\d{2}){4}|\d{2}(?:[\s.-]?\d{3}){2})/;

class Is 
{
  email(text) {
    return text.match(EMAIL_REGEXP) ? true : false;
  }

  phone(text) {
    return text.match(PHONE_REGEXP) ? true : false;
  }
}

export default new Is();