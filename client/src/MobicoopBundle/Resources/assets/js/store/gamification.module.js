export const gamification = {
  namespaced: true,
  state: {
    active: false,
    gamificationNotifications: [],
    userAccept: true
  },
  mutations: {
    updateGamificationNotifications (state, gamificationNotifications) {
      state.gamificationNotifications = gamificationNotifications;
    },
    setActive (state, active) {
      state.active = active;
    },
    setUserAccept (state, userAccept) {
      state.userAccept = userAccept;
    }    
  },
  getters: {
    gamificationNotifications (state) {
      return state.gamificationNotifications;
    },
    isActive (state) {
      return state.active;
    },
    hasUserAccept (state) {
      return state.userAccept;
    }
  } 
}