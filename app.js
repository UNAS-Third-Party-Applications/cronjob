/**
 *Cron Job App
 * Defined an App to manage crontab jobs
 */
var CronJobApp = CronJobApp || {} //Define CronJob App namespace.
/**
 *Constructor UNAS App
 */
CronJobApp.App = function () {
  this.id = 'Cron Job'
  this.name = 'Cron Job'
  this.version = '6.0.2'
  this.active = false
  this.menuIcon = '/apps/cronjob/images/logo.png?v=6.0.2&'
  this.shortcutIcon = '/apps/cronjob/images/logo.png?v=6.0.2&'
  this.entryUrl = '/apps/cronjob/cronjob.html?v=6.0.2&'
  var self = this
  this.CronJobAppWindow = function () {
    if (UNAS.CheckAppState('Cron Job')) {
      return false
    }
    self.window = new MUI.Window({
      id: 'CronJobAppWindow',
      title: UNAS._('Cron Job'),
      icon: '/apps/cronjob/images/logo_small.png?v=6.0.2&',
      loadMethod: 'xhr',
      width: 750,
      height: 480,
      maximizable: false,
      resizable: true,
      scrollbars: false,
      resizeLimit: { x: [200, 2000], y: [150, 1500] },
      contentURL: '/apps/cronjob/cronjob.html?v=6.0.2&',
      require: { css: ['/apps/cronjob/css/index.css'] },
      onBeforeBuild: function () {
        UNAS.SetAppOpenedWindow('Cron Job', 'CronJobAppWindow')
      },
    })
  }
  this.CronJobUninstall = function () {
    UNAS.RemoveDesktopShortcut('Cron Job')
    UNAS.RemoveMenu('Cron Job')
    UNAS.RemoveAppFromGroups('Cron Job', 'ControlPanel')
    UNAS.RemoveAppFromApps('Cron Job')
  }
  new UNAS.Menu(
    'UNAS_App_Internet_Menu',
    this.name,
    this.menuIcon,
    'Cron Job',
    '',
    this.CronJobAppWindow
  )
  //new UNAS.DesktopShortcut(this.name, this.shortcutIcon, 'Cron Job', this.CronJobAppWindow);
  new UNAS.RegisterToAppGroup(
    this.name,
    'ControlPanel',
    {
      Type: 'Internet',
      Location: 1,
      Icon: this.shortcutIcon,
      Url: this.entryUrl,
    },
    {}
  )
  var OnChangeLanguage = function (e) {
    UNAS.SetMenuTitle('Cron Job', UNAS._('Cron Job')) //translate menu
    //UNAS.SetShortcutTitle('Cron Job', UNAS._('Cron Job'));
    if (typeof self.window !== 'undefined') {
      UNAS.SetWindowTitle('CronJobAppWindow', UNAS._('Cron Job'))
    }
  }
  UNAS.LoadTranslation(
    '/apps/cronjob/languages/Translation?v=' + this.version,
    OnChangeLanguage
  )
  UNAS.Event.addEvent('ChangeLanguage', OnChangeLanguage)
  UNAS.CreateApp(
    this.name,
    this.shortcutIcon,
    this.CronJobAppWindow,
    this.CronJobUninstall
  )
}

new CronJobApp.App()
