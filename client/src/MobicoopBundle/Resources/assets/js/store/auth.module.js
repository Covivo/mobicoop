export const auth = {
  namespaced: true,
  state: {
    token: ''
  },
  mutations: {
    setToken (state, token) {
      state.token = token;
    }
  },
  getters: {
    token (state) {
      return state.token;
    }
  } 
}