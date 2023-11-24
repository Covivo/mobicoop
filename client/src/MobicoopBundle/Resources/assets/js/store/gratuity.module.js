export const gratuity = {
  namespaced: true,
  state: {
    active: false
  },
  mutations: {
    setActive (state, active) {
      state.active = active;
    }
  },
  getters: {
    isActive (state) {
      return state.active;
    }
  }
}
