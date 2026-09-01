import 'package:flutter/material.dart';

import '../theme/app_colors.dart';

class HanovaSpacing {
  static const double xs = 4;
  static const double sm = 8;
  static const double md = 12;
  static const double lg = 16;
  static const double xl = 24;
  static const double xxl = 32;

  const HanovaSpacing._();
}

class HanovaRadii {
  static const double control = 16;
  static const double card = 22;
  static const double sheet = 28;

  const HanovaRadii._();
}

class HanovaSurface extends StatelessWidget {
  final Widget child;
  final EdgeInsetsGeometry padding;
  final EdgeInsetsGeometry? margin;
  final Color color;

  const HanovaSurface({
    super.key,
    required this.child,
    this.padding = const EdgeInsets.all(HanovaSpacing.lg),
    this.margin,
    this.color = AppColors.surface,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: margin,
      padding: padding,
      decoration: BoxDecoration(
        color: color,
        borderRadius: BorderRadius.circular(HanovaRadii.card),
        border: Border.all(color: AppColors.divider, width: .7),
        boxShadow: const [
          BoxShadow(
            color: AppColors.cardShadow,
            blurRadius: 22,
            offset: Offset(0, 8),
          ),
        ],
      ),
      child: child,
    );
  }
}

class HanovaSectionHeader extends StatelessWidget {
  final String title;
  final String? subtitle;
  final Widget? action;

  const HanovaSectionHeader({
    super.key,
    required this.title,
    this.subtitle,
    this.action,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(title, style: Theme.of(context).textTheme.titleLarge),
              if (subtitle != null) ...[
                const SizedBox(height: HanovaSpacing.xs),
                Text(subtitle!, style: Theme.of(context).textTheme.bodyMedium),
              ],
            ],
          ),
        ),
        if (action != null) ...[
          const SizedBox(width: HanovaSpacing.md),
          action!,
        ],
      ],
    );
  }
}

class HanovaLoadingView extends StatelessWidget {
  final String? label;

  const HanovaLoadingView({super.key, this.label});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(HanovaSpacing.xxl),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const SizedBox(
              width: 32,
              height: 32,
              child: CircularProgressIndicator(strokeWidth: 2.6),
            ),
            if (label != null) ...[
              const SizedBox(height: HanovaSpacing.lg),
              Text(label!, style: Theme.of(context).textTheme.bodyMedium),
            ],
          ],
        ),
      ),
    );
  }
}

class HanovaStateView extends StatelessWidget {
  final IconData icon;
  final String title;
  final String? message;
  final String? actionLabel;
  final VoidCallback? onAction;
  final Color iconColor;

  const HanovaStateView({
    super.key,
    required this.icon,
    required this.title,
    this.message,
    this.actionLabel,
    this.onAction,
    this.iconColor = AppColors.primary,
  });

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(HanovaSpacing.xl),
        child: ConstrainedBox(
          constraints: const BoxConstraints(maxWidth: 360),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 82,
                height: 82,
                decoration: BoxDecoration(
                  color: iconColor.withValues(alpha: .1),
                  borderRadius: BorderRadius.circular(26),
                ),
                child: Icon(icon, size: 38, color: iconColor),
              ),
              const SizedBox(height: HanovaSpacing.lg),
              Text(
                title,
                textAlign: TextAlign.center,
                style: Theme.of(context).textTheme.titleLarge,
              ),
              if (message != null) ...[
                const SizedBox(height: HanovaSpacing.sm),
                Text(
                  message!,
                  textAlign: TextAlign.center,
                  style: Theme.of(
                    context,
                  ).textTheme.bodyMedium?.copyWith(height: 1.55),
                ),
              ],
              if (actionLabel != null && onAction != null) ...[
                const SizedBox(height: HanovaSpacing.xl),
                SizedBox(
                  width: 220,
                  child: ElevatedButton(
                    onPressed: onAction,
                    child: Text(actionLabel!),
                  ),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}

Future<bool> showHanovaConfirmDialog({
  required BuildContext context,
  required String title,
  required String message,
  required String confirmLabel,
  required String cancelLabel,
  bool destructive = false,
}) async {
  return await showDialog<bool>(
        context: context,
        builder: (dialogContext) => AlertDialog(
          icon: Icon(
            destructive
                ? Icons.delete_outline_rounded
                : Icons.help_outline_rounded,
            color: destructive ? AppColors.danger : AppColors.primary,
            size: 32,
          ),
          title: Text(title, textAlign: TextAlign.center),
          content: Text(message, textAlign: TextAlign.center),
          actionsAlignment: MainAxisAlignment.center,
          actions: [
            OutlinedButton(
              onPressed: () => Navigator.pop(dialogContext, false),
              child: Text(cancelLabel),
            ),
            ElevatedButton(
              style: ElevatedButton.styleFrom(
                minimumSize: const Size(110, 48),
                backgroundColor: destructive ? AppColors.danger : null,
              ),
              onPressed: () => Navigator.pop(dialogContext, true),
              child: Text(confirmLabel),
            ),
          ],
        ),
      ) ??
      false;
}
