<template>
  <v-toolbar>
    <v-toolbar-title>
      <img
        class="logo"
        src="/images/logo.png"
        alt="Mobicoop"
      >
    </v-toolbar-title>
    <v-spacer />
    <v-toolbar-items class="hidden-sm-and-down">
      <v-btn
        rounded
        href="/utilisateur/inscription"
      >
        Inscription
      </v-btn>
      <v-btn
        rounded="true"
        href="/utilisateur/connexion"
      >
        Connexion
      </v-btn>
      <v-btn
        rounded
        href="/covoiturage/annonce/poster"
      >
        Publier une annonce
      </v-btn>
    </v-toolbar-items>
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
      { icon: 'home', title: 'Inscription', link: 'localhost:8081/utilisateur/inscription' },
      { icon: 'info', title: 'Connexion', link: 'localhost:8081/utilisateur/connexion' },
      { icon: 'warning', title: 'Publier une annonce', link:'localhost:8081/covoiturage/annonce/poster' }
    ]
  }),

  methods: {
  }
}
</script>