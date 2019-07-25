<template>
  <v-toolbar>
    <v-toolbar-title>
      <a href="/"><img
        class="logo"
        src="/images/logo.png"
        alt="Mobicoop"
      ></a>
    </v-toolbar-title>
    <v-spacer />
    <v-btn
      class="signup"
      href="/utilisateur/inscription"
    >
      Inscription
    </v-btn>
    <v-btn
      rounded
      class="connexion"
    >
      Connexion
    </v-btn>
    <v-btn
      class="shareAd"
      href="/covoiturage/annonce/poster"
    >
      Publier une annonce
    </v-btn>
    <v-menu class="hidden-md-and-up">
      <v-toolbar-side-icon slot="activator" />
      <v-list>
        <v-list-tile
          v-for="item in menu"
          :key="item.icon"
        >
          <v-list-tile-content>
            <v-list-tile-title>{{ item.title }}</v-list-tile-title>
          </v-list-tile-content>
        </v-list-tile>   
      </v-list>
    </v-menu>
  </v-toolbar>
</template>
<script>
export default {
  data: () => ({
    menu: [
      { title: 'Inscription', link: 'localhost:8081/utilisateur/inscription' },
      { title: 'Connexion', link: '/utilisateur/connexion' },
      { title: 'Publier une annonce', link:'/covoiturage/annonce/poster' }
    ]
  }),

  methods: {
  }
}
</script>