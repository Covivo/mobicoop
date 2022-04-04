export const messages = {
  namespaced: true,
  state: {
    unreadCarpoolMessageNumber: 0,
    unreadDirectMessageNumber: 0,
    unreadSolidaryMessageNumber: 0
  },
  mutations: {
    setUnreadCarpoolMessageNumber (state, unreadCarpoolMessageNumber) {
      state.unreadCarpoolMessageNumber = unreadCarpoolMessageNumber;
    },
    setUnreadDirectMessageNumber (state, unreadDirectMessageNumber) {
      state.unreadDirectMessageNumber = unreadDirectMessageNumber;
    },
    setUnreadSolidaryMessageNumber (state, unreadSolidaryMessageNumber) {
      state.unreadSolidaryMessageNumber = unreadSolidaryMessageNumber;
    }
  },
  getters: {
    unreadCarpoolMessageNumber (state) {
      return state.unreadCarpoolMessageNumber;
    },
    unreadDirectMessageNumber (state) {
      return state.unreadDirectMessageNumber;
    },
    unreadSolidaryMessageNumber (state) {
      return state.unreadSolidaryMessageNumber;
    }
  }
}
