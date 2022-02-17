export const userPrefs = {
  namespaced: true,
  state: {
    connectionActive: false,
    social: false,
    socialCookies: [],
    stats: false
  },
  mutations: {
    updateConnectionActive (state, connectionActive) {
      state.connectionActive = connectionActive;
    },
    updateSocial (state, social) {
      state.social = social;
    },
    updateSocialCookies (state, socialCookies) {
      state.socialCookies = socialCookies;
    },
    updateStats (state, stats) {
      state.stats = stats;
    }
  },
  getters: {
    connectionActive (state) {
      return state.connectionActive;
    },
    social (state) {
      return state.social;
    },
    stats (state) {
      return state.stats;
    },
    socialCookies (state) {
      return state.socialCookies;
    }
  } 
}