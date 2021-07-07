export const gamificationNotifications = {
  namespaced: true,
  state: {
    gamificationNotifications: []
  },
  mutations: {
    updateGamificationNotifications (state, gamificationNotifications) {
      state.gamificationNotifications = gamificationNotifications;
    }
  },
  getters: {
    gamificationNotifications (state) {
      return state.gamificationNotifications;
    }
  } 
}