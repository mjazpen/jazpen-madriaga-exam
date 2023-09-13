<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Youtube API Technical Exam</title>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vuetify@3.3.15/dist/vuetify.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/7.2.96/css/materialdesignicons.min.css">
</head>
<body>
<div id="app">

  <v-dialog
      v-model="dialog"
      fullscreen
      :scrim="false"
      transition="dialog-bottom-transition"
    >
      <template v-slot:activator="{ props }">
        <v-btn
          color="white"
          dark
          v-bind="props"
        >
          Open Dialog
        </v-btn>
      </template>
      <v-card>
        <v-toolbar
        >
          <v-btn
            icon
            dark
            @click="dialog = false"
          >
            <v-icon>mdi-close</v-icon>
          </v-btn>
          <v-toolbar-title>ADD CHANNEL</v-toolbar-title>
        </v-toolbar>
        <v-card-text>
          <v-container>
            <v-row>
              <v-col cols="12">
                <v-text-field
                  label="Channel ID"
                  required
                  variant="outlined"
                  v-model="new_channel_id"
                ></v-text-field>
              </v-col>
              <v-col cols="12">
                <v-btn
                variant="outlined"
                color="light-blue-darken-4"
                class="mr-2"
                rounded
                @click = "dialog = false; channel_id = new_channel_id; get_data(); new_channel_id = '';" 
                >
                  SAVE
                </v-btn>
                <v-btn
                variant="outlined"
                @click = "dialog = false"
                rounded
                >
                  CANCEL
                </v-btn>
              </v-col>
            </v-row>
          </v-container>
        </v-card-text>
      </v-card>
    </v-dialog>

    <v-progress-linear
      indeterminate
      color="orange-darken-2"
      style="position: fixed; top: 0; z-index: 99999;"
      v-if="is_loading"
      size="5"
    ></v-progress-linear>
    
    <v-layout>
      <!-- <v-system-bar color="deep-purple darken-3"></v-system-bar> -->

      <v-app-bar
        color="white"
        prominent
        elevation="0"
      >
        <v-app-bar-nav-icon variant="text" @click.stop="drawer = !drawer"></v-app-bar-nav-icon>

        <v-toolbar-title class="text-body-1">Technical Exam</v-toolbar-title>

        <v-spacer></v-spacer>

        <v-btn 
        variant="text" 
        icon="mdi-plus"
        @click = "dialog = true"
        ></v-btn>
      </v-app-bar>

      <v-navigation-drawer
        v-model="drawer"
        temporary
      >
      <v-list>
        <v-list-item
          v-for="(item, index) in items"
          :key="index"
          @click="channel_id = item.value; get_data(); drawer = false"
        >
          {{ item.text }}
        </v-list-item>
      </v-list>
      </v-navigation-drawer>
      <v-main class="mt-3">
        <v-container >
          <v-sheet class="d-block d-sm-flex pb-3 ">
            <v-avatar
            size="200"
            class="d-none d-sm-block mr-4"
            >
              <v-img
                :src="channel_info.profile_picture"
              ></v-img>
            </v-avatar>
            <div class="my-auto">

              <h1 class="text-h4 d-flex align-center mb-2 mb-sm-1">
              <p>

              <v-avatar
                class="d-block d-sm-none mr-4"
                >
                  <v-img
                    :src="channel_info.profile_picture"
                  ></v-img>
                </v-avatar>
              </p>
              <p>

              {{ channel_info.name }}
              </p>
              </h1>
              <p class="text-body-1">
                {{ truncated_desc }}
                <v-btn icon="mdi-chevron-double-right" elevation="0" @click="tab = 2"></v-btn>
              </p>

              
              <hr>
            </div>
          </v-sheet>
          <v-tabs
          v-model="tab"
          >
            <v-tab :value="1">Home</v-tab>
            <v-tab :value="2">About</v-tab>
          </v-tabs>
          <v-window v-model="tab">
            <v-window-item
              :value="1"
            >
              <v-container fluid>
                
                <v-row >
                  <v-col 
                  v-for="video in videos"
                  cols="12"
                  md="12"
                  sm="6"
                  style="border-bottom: 1px solid #eee;"
                  >

                  <v-hover v-slot="{ isHovering, props }">
                    <a
                    :href="video.link"
                    class="text-decoration-none text-black"
                    >

                      <v-row class="mb-3">
                        <v-col 
                        cols="12" 
                        md="4"
                        >
                          <v-img
                            :src="video.thumbnail"
                            min-width="250px"
                            class="rounded-lg"
                            cover
                            v-bind="props" 

                          >
                        
                          <v-overlay
                            :model-value="isHovering"
                            contained
                            scrim="#000"
                            class="align-center justify-center"
                          >
                            <v-btn icon="mdi-play" elevation="0"></v-btn>
                          </v-overlay>
                        </v-img>
                        </v-col>
                        <v-col 
                        class="mx-4 py-4"
                        
                        v-bind="props" 
                        >
                          <v-row
                          
                          >
                            <v-col cols="12" >
                              <p class="text-lg-h4 text-sm-h6 text-h6 ">{{ video.title }}</p>
                            </v-col>
                            <v-col cols="12" class="d-none d-md-block">
                              <p 
                              class="text-body-1 text-sm-body-2 text-body-2 text-break " 
                              
                              style="max-height: 50px; "  
                              >{{ video.description }}</p>
                            </v-col>
                            <v-col cols="12" >
                              <p class="text-body-2 text-md-body-2 text-grey-darken-1 " >{{ channel_info.name }}</p>
                            </v-col>
                          </v-row>
                        </v-col>
                      </v-row>
                    </a>
                  </v-hover>
                  </v-col>
                </v-row>
                  
                <v-row justify="center">
                  <v-col cols="12">
                    <v-container class="max-width">
                      <v-pagination
                        v-model="page"
                        class="my-4"
                        size="small"
                        :length="totalPages"
                      ></v-pagination>
                    </v-container>
                  </v-col>
                </v-row>
              </v-container>
            </v-window-item>
            <v-window-item
              :value="2"
            >
            <v-container>
            <p>
              {{ channel_info.description }}
            </p>
            </v-container>

            </v-window-item>
          </v-window>
        </v-container>
      </v-main>
    </v-layout>
</div>

<script src="https://cdn.jsdelivr.net/npm/vuetify@3.3.15/dist/vuetify.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/src/core.min.js"></script>
<script>
  const { createApp, ref, watch, onMounted,computed } = Vue
  const { createVuetify } = Vuetify

  const vuetify = createVuetify()

  createApp({
    setup() {
      const page           = ref(1)
      const totalPages     = ref(1)
      const videos         = ref([])
      const is_loading     = ref(false)
      const channel_info   = ref(
        {
          name: '',
          description: '',
          profile_picture: ''
        }
      )
      const drawer        = ref(false)
      const items         = ref([])
      const dialog        = ref(false)
      const new_channel_id = ref('')
      const channel_id     = ref('UCXuqSBlHAE6Xw-yeJA0Tunw')
      const show           = ref(false)
      const tab            = ref(1)

      // COMPUTED
      const truncated_desc = computed(() => {
        return channel_info.value.description.substring(0, 150) + '...'
      })

      // WATCHERS
      watch(page, (newVal, oldVal) => {
        get_data()
      })

      // METHODS

      function get_data(){
        is_loading.value = true
        fetch('http://localhost/technical_exam/youtube_channel_json.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            channel_id: channel_id.value,
            page: page.value
          })
        })
        .then(response => response.json())
        .then(data => {
          totalPages.value    = data.data.videos.pagination.length
          videos.value        = data.data.videos.data
          channel_info.value  = data.data.info
        })
        .catch(error => {
          console.error(error)
        })
        .finally(() => {
          is_loading.value = false
          get_all_channels()
        })
      }

      function get_all_channels(){
        is_loading.value = true
        fetch('http://localhost/technical_exam/youtube_channel_json.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            all_channel: true
          })
        })
        .then(response => response.json())
        .then(data => {
          items.value = data.data.channels.map((item) => {
            return {
              text: item.name,
              value: item.channel_id
            }
          })
        })
        .catch(error => {
          console.error(error)
        })
        .finally(() => {
          is_loading.value = false
        })
      }

      // mounted
      onMounted(() => {
        get_data()
        get_all_channels()
      })

      return {
        page,
        totalPages,
        videos,
        channel_info,
        channel_id,
        is_loading,
        show,
        truncated_desc,
        tab,
        drawer,
        items,
        dialog,
        new_channel_id,

        // methods
        get_data,
        get_all_channels
      }
    }
  }).use(vuetify).mount('#app')
</script>
</body>
</html>