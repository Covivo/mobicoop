const DEFAULT_NODE = 'mobConnect';
const DEFAULT_INCENTIVE_URI = '/user/sso/eec-incentive';

export class Incentive {
  baseUri = null;
  redirectUrl = null;

  constructor() {
    this.baseUri = `${location.protocol}//${location.host}`;
    this.redirectUrl = new URL(`${this.baseUri}`);

    if (DEFAULT_INCENTIVE_URI === location.pathname) {
      this.init();
    }
  }

  init = () => {
    const strQuery = window.location.hash ? window.location.hash.substring(1) : null;

    if (strQuery) {
      const objQuery = this.parseQuery(strQuery);

      if (
        objQuery.hasOwnProperty('state')
                    && DEFAULT_NODE === objQuery.state
                    && objQuery.hasOwnProperty('code')
      ) {
        this.redirectUrl = new URL(`${this.baseUri}/user/sso/login?state=${objQuery.state}&code=${objQuery.code}&origin=mobConnect`);
      }
    }

    this.redirect();
  }

  parseQuery = (queryString) => {
    var query = {};
    var pairs = (queryString[0] === '?' ? queryString.substr(1) : queryString).split('&');
    for (var i = 0; i < pairs.length; i++) {
      var pair = pairs[i].split('=');
      query[decodeURIComponent(pair[0])] = decodeURIComponent(pair[1] || '');
    }
    return query;
  }

  redirect = () => {
    window.location.href = this.redirectUrl;
  }
}
