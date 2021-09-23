export const gamification = {
  namespaced: true,
  state: {
    active: false,
    gamificationNotifications: []
  },
  mutations: {
    updateGamificationNotifications (state, gamificationNotifications) {
      state.gamificationNotifications = gamificationNotifications;
    },
    setActive (state, active) {
      state.active = active;
    }    
  },
  getters: {
    gamificationNotifications (state) {
      return state.gamificationNotifications;
    },
    isActive (state) {
      return state.active;
    }
  } 
}