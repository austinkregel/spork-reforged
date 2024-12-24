<script setup>
import { computed } from 'vue';
import * as OutlinedIcons from '@heroicons/vue/24/outline'
import * as SolidIcons from '@heroicons/vue/24/solid'
const props = defineProps({
    iconName: String,
    active: Boolean
});

const icons = computed(() => {
  const activeIconSet = props.active ? SolidIcons : OutlinedIcons;
  return {
      ...activeIconSet,
      ChatBubbleLeftRightIcon: activeIconSet.ChatBubbleLeftRightIcon,
      ArticleIcon: activeIconSet.NewspaperIcon,
      ConditionIcon: activeIconSet.CodeBracketIcon,
      CredentialIcon: activeIconSet.LockClosedIcon,
      DomainIcon: activeIconSet.GlobeAsiaAustraliaIcon,
      ExternalRssFeedIcon: activeIconSet.RssIcon,
      AccountIcon: activeIconSet.BuildingOfficeIcon,
      BudgetIcon: activeIconSet.WalletIcon,
      NavigationIcon: activeIconSet.ArrowTopRightOnSquareIcon,
      PageIcon: activeIconSet.DocumentIcon,
      PersonIcon: activeIconSet.UsersIcon,
      ProjectIcon: activeIconSet.BuildingStorefrontIcon,
      ResearchIcon: activeIconSet.BeakerIcon,
      ScriptIcon: activeIconSet.CodeBracketSquareIcon,
      ThreadIcon: activeIconSet.ChatBubbleLeftRightIcon,
      ClipboardIcon: activeIconSet.ClipboardIcon,
      EmailIcon: activeIconSet.EnvelopeIcon,
      ArrowTopRightOnSquareIcon: activeIconSet.ArrowTopRightOnSquareIcon,
      AssetIcon: activeIconSet.ArchiveBoxIcon,
      DomainRecordIcon: activeIconSet.LinkIcon,
      TransactionIcon: activeIconSet.RectangleStackIcon,
      ShortCodeIcon: activeIconSet.QrCodeIcon,
      TaskIcon: activeIconSet.QueueListIcon
  }
})
</script>

<template>
    <Component
        :is="icons[props.iconName]"
    >
    </Component>
</template>
