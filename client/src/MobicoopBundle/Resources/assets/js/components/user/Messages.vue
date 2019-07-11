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
          v-for="(thread, index) in threads"
          :key="index"
          class="mx-auto"
          color="#26c6da"
          max-width="400"
          dark
        >
          <v-card-title>
            <i class="material-icons">
              account_circle
            </i>
            <span class="title font-weight-light white--text">{{ thread.contactFirstName }} {{ thread.contactLastName }}</span>
          </v-card-title>
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
            fil-dot
            :left="item.origin==='own'"
            :right="item.origin!=='own'"
          >
            <template v-slot:icon>
              <v-avatar>
                <i class="material-icons">
                  {{ item.icon }}
                </i>
              </v-avatar>
            </template>
            <template v-slot:opposite>
              <span>Tus eu perfecto</span>
            </template>
            <v-card class="elevation-2">
              <v-card-text>{{ item.text }}</v-card-text>
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
    threadsforview: {
      type: Array,
      default: function(){return []}
    }
  },
  data() {
    return {
      items: [
        {
          icon: 'account_circle',
          text:'Lorem ipsum dolor sit amet, no nam oblique veritus. Commune scaevola imperdiet nec ut, sed euismod convenire principes at. Est et nobis iisque percipit, an vim zril disputando voluptatibus, vix an salutandi sententiae.',
          origin:'own'
        },
        {
          icon: 'account_circle',
          text:'Lorem ipsum dolor sit amet, no nam oblique veritus. Commune scaevola imperdiet nec ut, sed euismod convenire principes at. Est et nobis iisque percipit, an vim zril disputando voluptatibus, vix an salutandi sententiae.',
          origin:'own'
        },
        {
          icon: 'account_circle',
          text:'Lorem ipsum dolor sit amet, no nam oblique veritus. Commune scaevola imperdiet nec ut, sed euismod convenire principes at. Est et nobis iisque percipit, an vim zril disputando voluptatibus, vix an salutandi sententiae.',
          origin:'contact'
        },
      ],
      threads: this.threadsforview
    }
  },
  mounted () {
  }
}
</script>