const email_regexp = /[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/;
const full_email_regexp = /^[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/;

const phone_regexp = /(?:(?:\+|00)[0-9]{2,3}[\s.-]{0,3}(?:\(0\)[\s.-]{0,3})?|0)[1-9](?:(?:[\s.-]?\d{2}){4}|\d{2}(?:[\s.-]?\d{3}){2})/;
const full_phone_regexp = /^(?:(?:\+|00)[0-9]{2,3}[\s.-]{0,3}(?:\(0\)[\s.-]{0,3})?|0)[1-9](?:(?:[\s.-]?\d{2}){4}|\d{2}(?:[\s.-]?\d{3}){2})$/;

/**
 * @author Olivier Fillol <olivier.fillol@mobicoop.org>
 */
class Is
{
  /**
   * Returns if the text is or contains an email address
   * @param {string} text
   * @param {boolean} fullChain
   * @returns {boolean}
   */
  email(text, fullChain = false) {
    const regexp = fullChain ? full_email_regexp : email_regexp;

    return regexp.test(text);
  }

  /**
   * Returns if the text is or contains a phone number
   * @param {string} text
   * @param {boolean} fullChain
   * @returns {boolean}
   */
  phone(text, fullChain = false) {
    const regexp = fullChain ? full_phone_regexp : phone_regexp;

    return regexp.test(text);
  }
}

export { email_regexp, phone_regexp };
export default new Is();
