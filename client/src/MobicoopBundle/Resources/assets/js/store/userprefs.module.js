export const userPrefs = {
  namespaced: true,
  state: {
    connectionActive: false,
    social: false,
    stats: false
  },
  mutations: {
    updateConnectionActive (state, connectionActive) {
      state.connectionActive = connectionActive;
    },
    updateSocial (state, social) {
      state.social = social;
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
    }
  } 
}