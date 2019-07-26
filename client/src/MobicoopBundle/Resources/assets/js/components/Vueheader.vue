<template>
  <div>
    <v-toolbar flat>
      <v-toolbar-title>
        <a href="/"><v-img
          class="logo"
          src="/images/logo.png"
          alt="Mobicoop"
        /></a>
      </v-toolbar-title>
      <v-spacer />
      <v-btn
        
        text
        :href="$t('navBar.buttons.signup.route')"
      >
        {{ $t('navBar.buttons.signup.label') }}
      </v-btn>
      <v-btn
        
        text
        :href="$t('navBar.buttons.connexion.route')"
      >
        {{ $t('navBar.buttons.connexion.label') }}
      </v-btn>
      <v-btn
        
        rounded
        :href="$t('navBar.buttons.shareAnAd.route')"
      >
        {{ $t('navBar.buttons.shareAnAd.label') }}
      </v-btn>
    </v-toolbar>
  </div>
</template>
<script>
export default {
  
}
</script>