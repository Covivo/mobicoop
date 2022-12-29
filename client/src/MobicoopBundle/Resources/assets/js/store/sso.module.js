export const sso = {
  namespaced: true,
  state: {
    ssoButtonsActiveStatus: {},
    refreshActiveButtons: false
  },
  mutations: {
    setSsoButtonsActiveStatus (state, ssoStatus) {
      state.ssoButtonsActiveStatus[ssoStatus.ssoId] = ssoStatus.status;
    },
    setRefreshActiveButtons (state, refreshActiveButtons) {
      state.refreshActiveButtons = refreshActiveButtons;
    }
  },
  getters: {
    ssoButtonsActiveStatus (state) {
      return state.ssoButtonsActiveStatus;
    },
    refreshActiveButtons (state) {
      return state.refreshActiveButtons;
    }
  }
}
