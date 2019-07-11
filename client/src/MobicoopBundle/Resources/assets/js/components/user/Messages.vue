<template>
  <v-container
    text-xs-center
    fill-height
  >
    <v-layout
      fill-height
    >
      <v-flex
        id="threadColumn"
        xs3
      >
        <!-- Threads -->



        <v-card
          class="mx-auto"
          color="#26c6da"
          max-width="400"
          dark
        >
          <v-card-title>
            <i class="material-icons">
              directions_car
            </i>
            <span class="title font-weight-light white--text">Jean-José C.</span>
          </v-card-title>

          <v-card-text class="headline font-weight-bold">
            Lyon <i class="fas fa-arrow-circle-right" /> Marseille
          </v-card-text>
        </v-card>
      </v-flex>
      <v-flex
        id="messagesColumn"
        xs6
      >
        <v-timeline align-top>
          <v-timeline-item
            v-for="(item, i) in items"
            :key="i"
            :color="item.color"
            :icon="item.icon"
            fill-dot
          >
            <v-card
              :color="item.color"
            >
              <v-card-title
                class="title white--text"
              >
                Jean-José C.
              </v-card-title>
              <v-card-text class="white text--primary">
                <p>{{ item.text }}</p>
              </v-card-text>
            </v-card>
          </v-timeline-item>
        </v-timeline>        
      </v-flex>
      <v-flex
        id="contextColumn"
        xs3
      >
        droite
      </v-flex>
    </v-layout>
  </v-container>
</template>
<script>
export default {
  props: {
    threads: {
      type: Object,
      default: function(){return {}}
    }
  },
  data: () => ({
    items: [
      {
        color: 'red lighten-2',
        icon: 'directions_car',
        text:'Lorem ipsum dolor sit amet, no nam oblique veritus. Commune scaevola imperdiet nec ut, sed euismod convenire principes at. Est et nobis iisque percipit, an vim zril disputando voluptatibus, vix an salutandi sententiae.'
      },
      {
        color: 'purple',
        icon: 'directions_car',
        text:'Lorem ipsum dolor sit amet, no nam oblique veritus. Commune scaevola imperdiet nec ut, sed euismod convenire principes at. Est et nobis iisque percipit, an vim zril disputando voluptatibus, vix an salutandi sententiae.'
      },
      {
        color: 'red lighten-2',
        icon: 'directions_car',
        text:'Lorem ipsum dolor sit amet, no nam oblique veritus. Commune scaevola imperdiet nec ut, sed euismod convenire principes at. Est et nobis iisque percipit, an vim zril disputando voluptatibus, vix an salutandi sententiae.'
      },
    ]
  }),
  mounted () {
    console.error("Toto");
    console.error(this.threads);
  }
}
</script>